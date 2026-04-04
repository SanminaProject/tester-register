# 改进路线图 - Tester Register API

**制定日期**: 2026-04-02  
**规划周期**: 4 周（包括生产部署）  
**总工作量**: ~61 小时 (P1: 10h, P2: 43h, P3: 8h)

---

## 概览

```
第 1 周 (部署前)
  ├─ P1 安全修复 (3 项)          ~6h    [关键路径]
  ├─ 部署文档                   2h    [关键路径]
  └─ 代码审查 + QA              2h

       ↓ 部署上线

第 2 周 (部署后立即)
  ├─ 审计日志中间件              4h
  ├─ EventLog 权限验证           3h
  ├─ 安全头设置                 1h
  └─ 并发测试 + 监控             2h

第 3-4 周 (持续优化)
  ├─ 性能测试与基准              12h
  ├─ 代码重构 (trait 提取)       4h
  ├─ 测试 Factory 补全           2h
  ├─ 文档自动化评估              8h
  └─ 缓存策略实施                6h

额外投资 (后续优化)
  ├─ 软删除实现                 5h
  ├─ APM 集成                  4h
  └─ 文档自动化 (Scribe)        8h
```

---

## 优先级分布

| 优先级   | 数量   | 工作量   | 风险等级 | 完成期限    |
| -------- | ------ | -------- | -------- | ----------- |
| **P0**   | 0      | 0h       | 🟢 无    | 已完成      |
| **P1**   | 4      | ~10h     | 🟠 中    | **部署前**  |
| **P2**   | 9      | ~43h     | 🟡 低    | 部署后 2 周 |
| **P3**   | 3      | ~8h      | 🔵 很低  | 后续优化    |
| **总计** | **16** | **~61h** |          |             |

---

## 第 1 周 - 生产部署准备 (关键路径)

### 核心目标

- 生产部署前修复所有 P1 安全问题
- 建立部署和运维文档
- QA 验证

### 任务列表

#### 1.1 🔴 添加 API 速率限制 (P1, 安全)

**问题**: 登录和注册端点无防暴力破解机制

**修复方案**:

```php
// routes/api.php - 第 20-25 行修改
Route::prefix('v1')->group(function () {
    // 公开端点 + 速率限制
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('/auth/login', [AuthController::class, 'login']);
        Route::post('/auth/register', [AuthController::class, 'register']);
    });

    // 受保护端点（已有 auth:sanctum）
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        // 其他路由...
    });
});
```

**验证步骤**:

```bash
# 1. 快速发送 7 个请求（超过 6/1 分钟限制）
for i in {1..7}; do
    curl -X POST http://localhost:8000/api/v1/auth/login \
        -H "Content-Type: application/json" \
        -d '{"email":"test@example.com","password":"password123"}'
done

# 预期: 第 7 个请求返回 429 Too Many Requests
```

**预期输出**:

```json
{
    "success": false,
    "message": "Too Many Requests",
    "code": 429
}
```

**工作量**: 1-2 小时  
**难度**: ⭐ 很低  
**优先级**: 🔴 关键

---

#### 1.2 🔴 配置令牌过期时间 (P1, 安全)

**问题**: Sanctum 令牌默认永不过期

**修复方案**:

```php
// config/sanctum.php - 查找并修改
'expiration' => 24 * 60,  // 24 小时（分钟数）
'extending_expiration' => true,  // 可选: 活动时自动延期

// 或更严格: 12 小时
'expiration' => 12 * 60,
```

**验证步骤**:

```php
// 新测试: tests/Feature/Api/TokenExpirationTest.php
class TokenExpirationTest extends TestCase {
    public function test_token_expires_after_24_hours() {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // 模拟 24 小时后...（需要手动设置 created_at）
        // 预期: 令牌无效
    }
}
```

**工作量**: 0.5-1 小时  
**难度**: ⭐ 很低  
**优先级**: 🔴 关键

---

#### 1.3 🟠 修复 EventLog 权限过宽松 (P1, 安全)

**问题**: 技术人员可创建任意日志，无用户/范围检查

**现状代码**:

```php
// app/Http/Requests/Api/StoreEventLogRequest.php
public function rules(): array {
    return [
        'tester_id' => 'required|exists:testers,id',
        'type' => 'required|in:maintenance,calibration,issue,repair,other',
        'description' => 'required|string|min:10',
        'event_date' => 'required|...',
        // ❌ 缺少 performed_by 和用户范围检查
    ];
}
```

**修复方案**:

```php
// app/Http/Requests/Api/StoreEventLogRequest.php (修改)
public function rules(): array {
    return [
        'tester_id' => 'required|exists:testers,id',
        'type' => 'required|in:maintenance,calibration,issue,repair,other',
        'description' => 'required|string|min:10',
        'event_date' => 'required|date|not_future',
        'performed_by' => 'nullable|string|max:100',  // 新增: 可选，默认用当前用户
    ];
}

// app/Http/Controllers/Api/EventLogController.php (修改)
public function store(StoreEventLogRequest $request): JsonResponse {
    $this->authorize('create', EventLog::class);

    $validated = $request->validated();

    // 强制记录当前用户（如果没有明确 performed_by）
    if (empty($validated['performed_by'])) {
        $validated['performed_by'] = $request->user()->name; // 当前用户
    }

    $eventLog = EventLog::create($validated);

    return response()->json([
        'success' => true,
        'message' => 'Event log created successfully',
        'data' => $eventLog,
        'code' => 201,
    ], 201);
}

// 可选: 添加用户签署
// $validated['user_id'] = $request->user()->id;  // 隐式关联
```

**测试**:

```php
// tests/Feature/Api/EventLogApiTest.php (添加)
public function test_event_log_records_current_user() {
    $user = User::factory()->create();
    $user->assignRole('Calibration Specialist');
    $tester = Tester::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/event-logs', [
        'tester_id' => $tester->id,
        'type' => 'maintenance',
        'description' => 'Routine maintenance completed',
        'event_date' => now()->subHour()->toDateTimeString(),
        // 不提供 performed_by，系统应自动填充
    ]);

    $response->assertStatus(201);
    $this->assertEquals($user->name, $response->json('data.performed_by'));
}
```

**工作量**: 2-3 小时  
**难度**: ⭐⭐ 低  
**优先级**: 🔴 关键

---

#### 1.4 📋 创建部署文档 (P1, 质量)

**文件**: docs/DEPLOYMENT.md

**模板**:

```markdown
# 部署指南 - Tester Register API

## 生产环境部署清单

### 前置条件

- PHP 8.3+ with Laravel 12
- MySQL 8.0+
- Redis (可选，缓存)

### 1. 环境配置

\`\`\`bash

# 复制环境文件

cp .env.example .env

# 生成应用密钥

php artisan key:generate

# 配置数据库

DB_CONNECTION=mysql
DB_HOST=<生产数据库主机>
DB_PORT=3306
DB_DATABASE=tester_register
DB_USERNAME=<用户名>
DB_PASSWORD=<密码>

# Sanctum 配置

SANCTUM_STATEFUL_DOMAINS=api.example.com
SESSION_DOMAIN=.example.com
\`\`\`

### 2. 数据库初始化

\`\`\`bash

# 迁移数据库

php artisan migrate --force

# 填充基础数据（主要是 Role）

php artisan db:seed RoleSeeder

# 创建默认用户（可选）

php artisan db:seed DatabaseSeeder
\`\`\`

### 3. 令牌过期配置

\`\`\`bash

# config/sanctum.php - 确保已设置

'expiration' => 24 \* 60, // 24 小时
\`\`\`

### 4. 速率限制配置

\`\`\`bash

# routes/api.php - 验证已添加 throttle:6,1

\`\`\`

### 5. 应用启动

\`\`\`bash

# 清除缓存

php artisan config:cache
php artisan route:cache

# 启动队列（如 EventLog 异步记录）

php artisan queue:work &

# 启动应用

php artisan serve --host=0.0.0.0 --port=8000
\`\`\`

### 6. 健康检查

\`\`\`bash

# API 健康状态

curl http://api.example.com/up

# 预期: OK

# 测试认证

curl http://api.example.com/api/v1/auth/login \
 -X POST \
 -H "Content-Type: application/json" \
 -d '{"email":"test@example.com","password":"password123"}'

# 预期: { "success": true, "message": "Login successful", ... }

\`\`\`

### 7. 日志位置

- 应用日志: `storage/logs/laravel.log`
- API 请求日志: `storage/logs/api.log` (部署后添加)

### 常见问题

**Q: 数据库连接错误**
A: 检查 DB_HOST, DB_USERNAME, DB_PASSWORD，确保防火墙允许 3306 端口

**Q: 令牌失效**
A: 检查 config/sanctum.php 中的 'expiration' 配置

**Q: 忘记速率限制**
A: 在 routes/api.php 中添加 \`middleware('throttle:6,1')\` 包装

**Q: 权限错误（role not found）**
A: 确保运行了 \`php artisan db:seed RoleSeeder\`
```

**工作量**: 2-3 小时  
**难度**: ⭐ 很低  
**优先级**: 🟡 高

---

#### 1.5 ✅ 代码审查与 QA (P1, 质量)

**任务**:

- [ ] Review 所有 P1 修复代码
- [ ] 执行手工测试 (curl + Postman)
- [ ] 执行自动化测试 (`php artisan test`)
- [ ] 验证所有功能端点仍正常
- [ ] 检查无敏感数据（密钥、密码）提交

**检查清单**:

```bash
# 1. 跑所有测试
php artisan test

# 预期: 165 passed ✅

# 2. 手工测试速率限制
# 发送 7 个请求到 /auth/login，验证第 7 个被 429 限制

# 3. 验证 EventLog 权限
# 创建 event log，验证 performed_by 正确填充

# 4. 检查环境敏感数据不在仓库
git status
# 确保 .env, .env.production 不在 git 中
```

**工作量**: 1-2 小时  
**难度**: ⭐ 很低  
**优先级**: 🟡 高

---

### 第 1 周小结

| 任务          | 工作量   | 状态 | 预期完成    |
| ------------- | -------- | ---- | ----------- |
| 速率限制      | 1-2h     | ⏳   | Day 1       |
| 令牌过期      | 0.5-1h   | ⏳   | Day 1       |
| EventLog 权限 | 2-3h     | ⏳   | Day 2       |
| 部署文档      | 2-3h     | ⏳   | Day 3       |
| 代码审查      | 1-2h     | ⏳   | Day 4       |
| **总计**      | **~10h** | ⏳   | **Day 4/5** |

**门槛**: 所有 5 个任务完成后，可部署上线 ✅

---

## 第 2 周 - 生产上线与运维加固

### 核心目标

- 安全部署到生产环境
- 实施审计日志
- 加强安全防护

### 任务列表

#### 2.1 🟠 添加 API 审计日志中间件 (P1, 质量)

**问题**: 无请求/响应日志，缺乏审计跟踪

**新建文件**: app/Http/Middleware/LogApiRequests.php

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        // 记录请求开始时间
        $startTime = microtime(true);

        $response = $next($request);

        // 计算响应时间
        $duration = (microtime(true) - $startTime) * 1000; // 毫秒

        // 仅记录 API 请求
        if ($request->is('api/*')) {
            Log::channel('api')->info('API Request', [
                'timestamp' => now()->toIso8601String(),
                'method' => $request->method(),
                'path' => $request->path(),
                'uri' => $request->getUri(),
                'status_code' => $response->status(),
                'duration_ms' => round($duration, 2),
                'user_id' => $request->user()?->id ?? 'anonymous',
                'user_email' => $request->user()?->email ?? 'anonymous',
                'ip_address' => $request->ip(),
                'request_size' => strlen($request->getContent()),
                'response_size' => strlen($response->getContent()),
            ]);
        }

        return $response;
    }
}
```

**注册中间件**: app/Http/Kernel.php (或 bootstrap/app.php)

```php
// bootstrap/app.php (Laravel 11+)
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(append: [
        \App\Http\Middleware\LogApiRequests::class,
    ]);
})
```

**配置日志通道**: config/logging.php

```php
'channels' => [
    // ...
    'api' => [
        'driver' => 'daily',
        'path' => storage_path('logs/api.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,  // 保留 14 天日志
    ],
],
```

**验证**:

```bash
# 发送 API 请求
curl -X GET http://localhost:8000/api/v1/users \
  -H "Authorization: Bearer <token>"

# 检查日志
tail -f storage/logs/api-2026-04-02.log

# 预期输出:
# [2026-04-02 10:30:45] api.INFO: API Request {
#   "method": "GET",
#   "path": "v1/users",
#   "status_code": 200,
#   "duration_ms": 45.32,
#   "user_id": 1,
#   "user_email": "admin@example.com"
# }
```

**工作量**: 3-4 小时  
**难度**: ⭐⭐ 低  
**优先级**: 🟠 高

---

#### 2.2 🟠 添加安全 HTTP 头 (P2, 安全)

**新建文件**: app/Http/Middleware/SecurityHeaders.php

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 防止浏览器 MIME 类型嗅探
        $response->header('X-Content-Type-Options', 'nosniff');

        // 防止 clickjacking
        $response->header('X-Frame-Options', 'DENY');

        // XSS 防护
        $response->header('X-XSS-Protection', '1; mode=block');

        // 内容安全策略（CSP）
        $response->header('Content-Security-Policy', "default-src 'self'; script-src 'self'");

        // 严格传输安全（HTTPS 强制）
        $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }
}
```

**注册**: bootstrap/app.php

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(append: [
        \App\Http\Middleware\SecurityHeaders::class,
    ]);
})
```

**验证**:

```bash
curl -I http://api.example.com/api/v1/auth/login

# 验证响应头包含:
# X-Content-Type-Options: nosniff
# X-Frame-Options: DENY
# X-XSS-Protection: 1; mode=block
```

**工作量**: 1-2 小时  
**难度**: ⭐ 很低  
**优先级**: 🟡 中等

---

#### 2.3 🟠 监控与告警配置 (P1, 质量)

**任务**: 建立基础监控

**工具**: 使用 Laravel 日志 + 简单告警

```php
// app/Observers/ErrorObserver.php (新建)
namespace App\Observers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ErrorObserver
{
    public function onException(\Throwable $e, Request $request): void
    {
        Log::error('API Error', [
            'exception' => class_basename($e),
            'message' => $e->getMessage(),
            'path' => $request->path(),
            'status' => 500,
            'timestamp' => now()->toIso8601String(),
        ]);

        // 可选: 发送告警通知
        // Notification::route('slack', env('SLACK_WEBHOOK_URL'))
        //     ->notify(new ApiErrorNotification($e));
    }
}
```

**告警规则** (可选集成):

- 5 分钟内 10+ 500 错误 → Slack 通知
- 5 分钟内 50+ 429 限流 → 发送告警邮件
- 数据库连接失败 → 立即告警

**工作量**: 1-2 小时（基础）  
**难度**: ⭐⭐ 低  
**优先级**: 🟡 中等

---

### 第 2 周小结

| 任务           | 工作量  | 状态 | 预期完成  |
| -------------- | ------- | ---- | --------- |
| 审计日志中间件 | 3-4h    | ⏳   | Day 1-2   |
| 安全头设置     | 1-2h    | ⏳   | Day 2     |
| 监控告警       | 1-2h    | ⏳   | Day 3     |
| 部署验证       | 1h      | ⏳   | Day 4     |
| **总计**       | **~8h** | ⏳   | **Day 4** |

---

## 第 3-4 周 - 质量与性能优化

### 核心目标

- 建立性能基准
- 优化代码结构
- 完善long-term 可维护性

### 优先级任务 (P2)

#### 3.1 性能测试与基准 (P2, 质量) - 12h

**任务**: 建立性能基准与告警阈值

**工具**: Apache Bench, Locust, 或 K6

**基准测试脚本**:

```bash
# 测试列表端点 (有 10000 条记录)
ab -n 1000 -c 10 \
  -H "Authorization: Bearer <token>" \
  http://api.example.com/api/v1/testers?page=1&per_page=50

# 分析结果:
# Requests per second (吞吐量)
# Time per request (平均响应时间)
# Failed requests (失败数)

# 预期基准:
# - 吞吐量: >= 100 req/s
# - p95 响应: <= 100ms
# - p99 响应: <= 200ms
# - 失败率: 0%
```

**性能测试计划**:

1. 列表端点: 100, 1000, 10000 条记录
2. 创建端点: 并发创建 (1, 10, 100 并发)
3. 更新端点: 并发更新同一资源
4. 认证端点: 暴力登录防护验证

**输出**: 性能基准报告 (docs/PERFORMANCE_BASELINE.md)

**工作量**: 12 小时  
**难度**: ⭐⭐⭐ 中等  
**优先级**: 🟡 高

---

#### 3.2 代码重构与提取 (P2, 质量) - 4h

**任务**: 减少代码重复，提高可维护性

**Trait 1: ApiResponse** (处理响应格式)

```php
// app/Traits/ApiResponse.php
trait ApiResponse
{
    protected function successResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'code' => $code,
        ], $code);
    }

    protected function listResponse($items, $total, $page, $perPage, $message = 'List retrieved')
    {
        return $this->successResponse([
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int)ceil($total / $perPage),
            ],
        ], $message, 200);
    }

    protected function errorResponse($message, $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
            'code' => $code,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
```

**使用示例**:

```php
// 从
return response()->json([
    'success' => true,
    'message' => 'Tester created successfully',
    'data' => $tester,
    'code' => 201,
], 201);

// 改为
return $this->successResponse($tester, 'Tester created successfully', 201);
```

**预期效果**:

- 减少 200+ 行重复的响应构造代码
- 统一响应格式，降低格式错误风险

**工作量**: 4 小时  
**难度**: ⭐⭐ 低  
**优先级**: 🟡 中等

---

#### 3.3 充分测试 Factory (P2, 质量) - 2h

**任务**: 为所有 8 个模型补全 Factory

**现状**: User, Tester 有 Factory，其他模型缺失

**新增**:

```php
// database/factories/TesterCustomerFactory.php
class TesterCustomerFactory extends Factory
{
    protected $model = TesterCustomer::class;

    public function definition(): array
    {
        return [
            'company_name' => $this->faker->unique()->company(),
            'address' => $this->faker->address(),
            'contact_person' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->companyEmail(),
        ];
    }
}

// 使用示例
$customer = TesterCustomer::factory()->create();
TesterCustomer::factory(5)->create();  // 创建 5 条
```

**类似补全**: FixtureFactory, MaintenanceScheduleFactory, ...

**工作量**: 2 小时  
**难度**: ⭐ 很低  
**优先级**: 🟡 中等

---

#### 3.4 文档自动化评估 (P2, 质量) - 8h

**任务**: 评估从 API_DESIGN.md 手工维护改为自动化生成

**选项 1: Laravel Scribe**

优点: 自动从代码生成文档
缺点: 需要学习配置

```bash
# 安装
composer require --dev knuckleswtf/scribe

# 生成
php artisan scribe:generate

# 输出: docs/api/index.html + docs/collection.json
```

**选项 2: OpenAPI 3.0 + Swagger UI**

优点: 行业标准，集成工具多
缺点: 需要手工编写 YAML

**选项 3: 保持手工维护**

优点: 灵活，控制粒度高
缺点: 易漂移

**建议**: 选择 Option 1 (Scribe)，工作量预计 8h 集成

**工作量**: 8 小时（评估 + 原型）  
**难度**: ⭐⭐⭐ 中等  
**优先级**: 🟡 中等（可后期）

---

#### 3.5 缓存策略实施 (P2, 性能) - 6h

**任务**: 减少数据库查询，提高性能

**缓存对象**:

1. 权限缓存: Role + Permission
2. 列表缓存: 分页结果
3. Config 缓存: 枚举值

**实现示例**:

```php
// 权限缓存
use Illuminate\Support\Facades\Cache;

// app/Traits/CachesPermissions.php
trait CachesPermissions
{
    public function cachedHasRole($role): bool
    {
        return Cache::remember(
            "user.{$this->id}.role.{$role}",
            3600,  // 1 小时
            fn() => $this->hasRole($role)
        );
    }
}

// 列表缓存
// app/Http/Controllers/Api/TesterController.php
public function index(ListTesterRequest $request): JsonResponse
{
    $validated = $request->validated();
    $cacheKey = 'testers.list.' . md5(json_encode($validated));

    $data = Cache::remember($cacheKey, 600, function () use ($validated) {
        // 执行查询
        return $this->buildQuery($validated)->paginate(...);
    });

    return $this->listResponse($data->items(), $data->total(), ...);
}
```

**缓存失效**:

- 创建/更新/删除 tester 后，清除相关缓存
- 权限变更后，清除用户缓存

**工作量**: 6 小时  
**难度**: ⭐⭐⭐ 中等  
**优先级**: 🟡 中等（可后期，基于性能测试）

---

### 第 3-4 周小结

| 任务         | 工作量  | 状态 | 优先级 |
| ------------ | ------- | ---- | ------ |
| 性能测试     | 12h     | ⏳   | 高     |
| 代码重构     | 4h      | ⏳   | 中等   |
| Factory 补全 | 2h      | ⏳   | 低     |
| 文档自动化   | 8h      | ⏳   | 中等   |
| 缓存策略     | 6h      | ⏳   | 中等   |
| **总计**     | **32h** | ⏳   |        |

---

## 额外投资 (后续优化)

### 4.1 软删除与审计日志 (P2) - 5h

```php
// 为核心模型添加 SoftDeletes
class Tester extends Model {
    use SoftDeletes;

    protected $dates = ['deleted_at'];
}

// 记录删除事件
protected static function booted(): void
{
    static::deleting(function (self $model) {
        EventLog::create([
            'type' => 'delete',
            'description' => "Tester {$model->model} deleted",
            'tester_id' => $model->id,
            'recorded_by' => auth()->user()->name ?? 'System',
        ]);
    });
}
```

### 4.2 APM 集成 (P3) - 4h

```php
// Sentry 错误追踪
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=<your-sentry-dsn>

// 或 New Relic
composer require newrelic/monolog-enricher
```

### 4.3 文档自动化完全实施 (P3) - 8h

```bash
# Scribe 完整集成 + 自定义样式
php artisan scribe:generate --force
```

---

## 里程碑与交付

```
第 1 周 (部署前)         ✅ P1 安全修复 + 部署文档
   ↓ [Gate: 所有测试通过]
生产部署                 🚀 上线
   ↓
第 2 周 (部署后)         ✅ 日志 + 安全头 + 监控
   ↓ [Gate: 生产监控就绪]
第 3-4 周 (优化)         ✅ 性能测试 + 代码优化 + 缓存
   ↓ [Gate: 性能基准建立]
长期运维                 ✅ 持续优化 + 文档自动化
```

---

## 资源与工作量总结

### 总体投入

| 阶段             | 工作量   | 人力       | 风险            |
| ---------------- | -------- | ---------- | --------------- |
| 第 1 周 (部署前) | 10h      | 1 人       | 🔴 关键         |
| 第 2 周 (上线)   | 8h       | 1 人       | 🟠 高           |
| 第 3-4 周        | 32h      | 1-2 人     | 🟡 低           |
| **总计**         | **~50h** | **1-2 人** | **<2 周内完成** |

### 预期收益

| 收益         | 量化              | 时间表  |
| ------------ | ----------------- | ------- |
| 消除安全风险 | 3 项中等风险      | 第 1 周 |
| 建立审计链   | 100% API 请求日志 | 第 2 周 |
| 性能基准     | p95/p99 响应时间  | 第 3 周 |
| 代码质量     | -30% 重复代码     | 第 4 周 |
| 可维护性     | +20% 开发效率     | 第 4 周 |

---

**路线图完成日期**: 2026-04-02  
**下一步**: 按照每周任务推进，每周末进行检查点评审
