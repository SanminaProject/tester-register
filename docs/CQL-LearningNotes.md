# Tester Register 项目完整学习笔记

> **面向计算机初学者的深度项目解析**  
> 本笔记涵盖项目架构、代码逐行注释、完整流程图示和业务模型

---

## 📋 目录导航

1. [项目概述](#项目概述)
2. [架构与目录结构](#架构与目录结构)
3. [核心概念解析](#核心概念解析)
4. [逐文件导读版](#逐文件导读版)
5. [路由与流程详解](#路由与流程详解)
6. [Livewire与前端JS协作](#livewire与前端js协作)
7. [完整代码注释版](#完整代码注释版)
8. [数据库与业务模型](#数据库与业务模型)
9. [权限系统与Seeder](#权限系统与seeder)
10. [模拟数据位置](#模拟数据位置)

---

## 项目概述

### 这是什么项目？

**Tester Register** 是一个用 **Laravel 11** 框架搭建的 Web 应用，专门用于管理和追踪仪器（Tester）的全生命周期。

**核心功能：**
- 用户认证系统（注册、登录、密码重置）
- 仪表板（实时消息面板）
- 仪器和配件管理
- 维护和校准计划追踪
- 事件日志记录
- 基于角色的权限控制

**技术栈一览：**

| 技术 | 用途 | 说明 |
|------|------|------|
| **Laravel 11** | 后端框架 | PHP 服务器端开发 |
| **Livewire** | 前端交互 | 让 PHP 驱动前端，无需写 JS |
| **Volt** | 简化语法 | Livewire 的轻量级语法 |
| **Tailwind CSS** | 样式框架 | 实用优先的 CSS 框架 |
| **Spatie Permission** | 权限管理 | 提供角色和权限系统 |
| **FullCalendar** | 日历库 | JS 日历组件 |
| **MySQL/SQLite** | 数据库 | 存储所有数据 |

### 项目目标

1. **集中管理** - 组织所有仪器及其附件信息
2. **事件追踪** - 记录维护、校准、修复等每个事件
3. **计划安排** - 可视化显示即将进行的任务
4. **权限控制** - 不同用户拥有不同的访问权限

---

## 架构与目录结构

### 整体架构流图

```
┌─────────────────────────────────────────────────────────┐
│                    用户浏览器                            │
└──────────────────────┬──────────────────────────────────┘
                       │ HTTP 请求
                       ↓
┌─────────────────────────────────────────────────────────┐
│            Laravel 应用服务器 (PHP)                    │
│  ┌────────────────────────────────────────────────────┐ │
│  │ 路由层 (routes/)                                  │ │
│  │ ├─ web.php    → 网页路由                          │ │
│  │ ├─ auth.php   → 认证路由                          │ │
│  │ └─ api.php    → API 路由                          │ │
│  └────────────────────────────────────────────────────┘ │
│                       ↓                                   │
│  ┌────────────────────────────────────────────────────┐ │
│  │ 中间件 (Middleware)                               │ │
│  │ ├─ auth      → 检查用户是否登录                  │ │
│  │ ├─ verified  → 检查邮箱是否验证                  │ │
│  │ └─ role:Admin → 检查是否有 Admin 角色              │ │
│  └────────────────────────────────────────────────────┘ │
│                       ↓                                   │
│  ┌────────────────────────────────────────────────────┐ │
│  │ Livewire 组件 / 控制器                            │ │
│  │ ├─ EventBox.php      → 事件盒子组件              │ │
│  │ ├─ Calendar.php      → 日历组件                  │ │
│  │ └─ EditUserRoles.php → 角色管理组件              │ │
│  └────────────────────────────────────────────────────┘ │
│                       ↓                                   │
│  ┌────────────────────────────────────────────────────┐ │
│  │ 模型层 (Models)                                   │ │
│  │ ├─ User              → 用户                      │ │
│  │ ├─ Tester            → 仪器                      │ │
│  │ ├─ TesterCustomer    → 客户                      │ │
│  │ ├─ MaintenanceSchedule → 维护计划                │ │
│  │ ├─ CalibrationSchedule → 校准计划                │ │
│  │ └─ EventLog          → 事件日志                  │ │
│  └────────────────────────────────────────────────────┘ │
│                       ↓                                   │
│  ┌────────────────────────────────────────────────────┐ │
│  │ 数据库 (MySQL/SQLite)                            │ │
│  │ ├─ users 表                                      │ │
│  │ ├─ roles 表                                      │ │
│  │ ├─ testers 表                                    │ │
│  │ └─ ... 等等 ...                                  │ │
│  └────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

### 目录树说明

```
tester-register/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/          ← 控制器（请求处理）
│   │   └── Requests/             ← 表单请求验证
│   │
│   ├── Livewire/                 ← Livewire 组件
│   │   ├── Pages/
│   │   │   ├── Dashboard/
│   │   │   │   ├── EventBox.php  ← 事件盒子组件（涉及模拟数据⚠️）
│   │   │   │   └── Calendar.php  ← 日历组件（涉及模拟数据⚠️）
│   │   │   └── Admin/
│   │   │       └── EditUserRoles.php ← 角色管理组件
│   │   └── Forms/
│   │       └── LoginForm.php     ← 登录表单逻辑
│   │
│   ├── Models/                   ← 数据模型
│   │   ├── User.php              ← 用户
│   │   ├── Tester.php            ← 仪器
│   │   ├── TesterCustomer.php    ← 客户
│   │   ├── Fixture.php           ← 配件
│   │   ├── MaintenanceSchedule.php ← 维护计划
│   │   ├── CalibrationSchedule.php ← 校准计划
│   │   ├── EventLog.php          ← 事件日志
│   │   └── SparePart.php         ← 配件库存
│   │
│   ├── Policies/                 ← 权限策略
│   │   ├── BasePolicy.php        ← 基础策略
│   │   ├── TesterPolicy.php      ← 仪器权限
│   │   └── ...
│   │
│   └── Providers/
│       ├── AppServiceProvider.php ← 应用服务注册
│       └── VoltServiceProvider.php ← Volt 注册
│
├── bootstrap/
│   └── app.php                   ← 应用初始化
│
├── config/
│   ├── app.php                   ← 应用配置
│   ├── auth.php                  ← 认证配置
│   ├── permission.php             ← Spatie Permission 配置
│   └── ...
│
├── database/
│   ├── migrations/               ← 数据库迁移
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2026_04_02_000101_create_tester_customers_table.php
│   │   ├── 2026_04_02_000102_create_testers_table.php
│   │   └── ...
│   └── seeders/                  ← 初始数据
│       ├── DatabaseSeeder.php    ← 入口
│       └── RoleSeeder.php        ← 创建角色和默认用户
│
├── public/
│   └── index.php                 ← 应用入口
│
├── resources/
│   ├── css/
│   │   └── app.css               ← 主样式
│   │
│   ├── js/
│   │   ├── app.js                ← 主 JS 入口
│   │   ├── bootstrap.js          ← Bootstrap（初始化）
│   │   └── calendar.js           ← FullCalendar 初始化
│   │
│   └── views/                    ← Blade 模板
│       ├── app.blade.php         ← 主布局
│       ├── dashboard.blade.php   ← 仪表板
│       ├── user-roles.blade.php  ← 角色管理页面
│       └── livewire/
│           ├── pages/
│           │   ├── auth/
│           │   │   ├── login.blade.php
│           │   │   ├── register.blade.php
│           │   │   └── ...
│           │   └── dashboard/
│           │       ├── event-box.blade.php
│           │       └── calendar.blade.php
│           └── pages/
│               └── admin/
│                   └── edit-user-roles.blade.php
│
├── routes/
│   ├── web.php                   ← 网页路由
│   ├── auth.php                  ← 认证路由
│   ├── api.php                   ← API 路由
│   ├── userRoles.php             ← 角色管理路由
│   └── ...
│
├── storage/                      ← 存储文件
│   ├── app/
│   ├── framework/
│   └── logs/
│
├── tests/                        ← 测试
│   ├── Feature/
│   └── Unit/
│
├── vendor/                       ← 第三方包
│
├── .env                          ← 环境变量
├── composer.json                 ← PHP 依赖管理
├── package.json                  ← JS 依赖管理
├── tailwind.config.js            ← Tailwind 配置
├── vite.config.js                ← Vite 打包配置
└── README.md                     ← 项目说明
```

---

## 核心概念解析

### 1. 什么是 Livewire？

**问题背景：** 传统 Web 开发比较复杂
```
HTML表单 → JavaScript → AJAX请求 → PHP处理 → 返回JSON → JS更新DOM → 用户看到
```

**Livewire 解决方案：** 让 PHP 直接驱动前端
```
HTML表单 → Livewire自动处理 → PHP方法执行 → 页面自动更新 → 用户看到
```

**Livewire 的优势：**
- ✅ 不用写 JavaScript（PHP 开发者友好）
- ✅ 响应式更新（输入框变化立即反映）
- ✅ 自动 AJAX 请求（开发者察觉不到）
- ✅ 组件化（复用代码）

### 2. 什么是 Volt？

**Volt** 是 Livewire 的轻量级语法糖。

**传统 Livewire：**
```
EventBox.php（PHP 类）+ event-box.blade.php（HTML 模板）
```

**Volt（简化版）：**
```
event-box.blade.php（同一个文件，既有 PHP 又有 HTML）
```

### 3. Blade 模板引擎

**Blade** 是 Laravel 的模板语言，让你在 HTML 中写 PHP。

**常见语法：**
```blade
<!-- 输出变量 -->
<h1>{{ $title }}</h1>

<!-- 条件 -->
@if ($user->isAdmin())
  <p>Admin panel</p>
@endif

<!-- 循环 -->
@foreach ($items as $item)
  <div>{{ $item->name }}</div>
@endforeach

<!-- 包含 Livewire 组件 -->
@livewire('pages.dashboard.event-box', ['title' => 'Events'])
```

### 4. 模型关系（Model Relationships）

**关系定义了表与表之间的联系：**

```
TesterCustomer (客户)
      ↓ 一对多
    Tester (仪器)
      ├─ 一对多 → Fixture (配件)
      ├─ 一对多 → MaintenanceSchedule (维护)
      ├─ 一对多 → CalibrationSchedule (校准)
      └─ 一对多 → EventLog (事件)
```

**使用例子：**
```php
// 一对多关系
$tester = Tester::find(1);
$customer = $tester->customer;           // 获取客户
$fixtures = $tester->fixtures;          // 获取所有配件
$events = $tester->eventLogs;           // 获取所有事件

// 反向关系
$customer = TesterCustomer::find(1);
$testers = $customer->testers;          // 获取所有仪器
```

### 5. 权限系统（Spatie Permission）

**Spatie Permission** 是第三方权限管理包。

**概念：**
- **角色（Role）**：Admin、Manager、Technician 等
- **权限（Permission）**：create、edit、delete、view 等
- **用户可以有多个角色**，每个角色有多个权限

**使用流程：**
```
1. 创建角色
   Role::create(['name' => 'Admin'])

2. 给用户分配角色
   $user->assignRole('Admin')

3. 检查权限
   if ($user->hasRole('Admin')) { ... }
```

---

## 逐文件导读版

### 🔀 路由文件（routes/）

指定 URL 如何映射到代码。

| 文件 | URL 模式 | 功能 | 中间件 |
|------|---------|------|--------|
| web.php | `/` | 首页 | 无 |
| web.php | `/dashboard` | 仪表板 | auth, verified |
| web.php | `/profile` | 个人资料 | auth |
| auth.php | `/login` | 登录页 | guest |
| auth.php | `/register` | 注册页 | guest |
| auth.php | `/forgot-password` | 忘记密码 | guest |
| userRoles.php | `/user-roles` | 角色管理 | auth, role:Admin |

### 🏛️ 模型文件（app/Models/）

代表数据库表的 PHP 类。

| 模型 | 对应表 | 主要字段 | 关键方法 |
|------|--------|---------|--------|
| User | users | id, email, password | hasRoles(), hasRole() |
| TesterCustomer | tester_customers | company_name, email | testers() |
| Tester | testers | model, serial_number, status | customer(), fixtures(), eventLogs() |
| Fixture | fixtures | name, serial_number | tester() |
| MaintenanceSchedule | maintenance_schedules | scheduled_date, status | tester() |
| CalibrationSchedule | calibration_schedules | scheduled_date, status | tester() |
| EventLog | event_logs | type, event_date | tester() |
| SparePart | spare_parts | name, quantity_in_stock | (独立表) |

### ⚙️ Livewire 组件（app/Livewire/）

处理前端交互的 PHP 类。

| 组件 | 功能 | 关键属性 | 关键方法 |
|------|------|---------|---------|
| EventBox | 显示事件列表 | $items, $type, $title | mount(), render() |
| Calendar | 显示日历 | $events | mount(), dispatch() |
| EditUserRoles | 管理用户角色 | $users, $roles | selectUser(), updateUserRole() |
| LoginForm | 处理登录 | $email, $password | authenticate() |

### 🎨 视图文件（resources/views/）

HTML 模板文件。

| 视图 | 显示者 | 包含组件 |
|------|--------|---------|
| dashboard.blade.php | Dashboard 页面 | EventBox × 2, Calendar |
| user-roles.blade.php | 角色管理页面 | EditUserRoles |
| login.blade.php | 登录页 | LoginForm |
| register.blade.php | 注册页 | RegisterForm |

### 🗄️ 数据库文件（database/）

定义数据表结构和初始数据。

| 文件 | 用途 |
|------|------|
| migrations/0001_01_01_000000_create_users_table.php | 创建 users 表 |
| migrations/2026_04_02_000102_create_testers_table.php | 创建 testers 表 |
| seeders/RoleSeeder.php | 创建角色和默认用户 |
| seeders/DatabaseSeeder.php | 主入口，调用其他 Seeder |

---

## 完整代码注释版

### 📄 routes/web.php

```php
<?php
use Illuminate\Support\Facades\Route;

// ============ 首页 ============
// URL: /
// 中间件: 无（任何人都可访问）
// 返回: welcome.blade.php 视图
Route::view('/', 'welcome');

// ============ 仪表板 ============
// URL: /dashboard
// 中间件:
//   'auth' - 必须已登录（Session 中要有用户 ID）
//   'verified' - 邮箱必须已验证（email_verified_at 不为 null）
// 返回: dashboard.blade.php 视图
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ============ 个人资料 ============
// URL: /profile
// 中间件: 'auth' - 必须已登录
// 返回: profile.blade.php 视图
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// ============ 引入其他路由文件 ============
// 这相当于把其他路由都加载进来
require __DIR__ . '/auth.php';        // /login, /register, /forgot-password 等
require __DIR__ . '/userRoles.php';   // /user-roles（Admin 专用）
require __DIR__ . '/testers.php';     // /testers, /testers/{id} 等
require __DIR__ . '/fixtures.php';    // /fixtures 等
require __DIR__ . '/issues.php';      // /issues 等
require __DIR__ . '/services.php';    // /services 等
?>
```

**关键概念：**
- `Route::view()` - 直接返回视图，不需要控制器
- `->middleware()` - 应用中间件（从左至右）
- `->name()` - 给路由起别名，方便引用（`route('dashboard')`）

---

### 📄 routes/auth.php

```php
<?php
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// ============ 游客专用路由 ============
// 中间件 'guest' 表示: 只有未登录的人才能访问
// 如果已登录，会被重定向到首页或上次访问的页面
Route::middleware('guest')->group(function () {

    // ---- 注册页 ----
    // URL: /register
    // Volt::route() 创建 Livewire 路由
    // 'pages.auth.register' 指向 resources/views/livewire/pages/auth/register.blade.php
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    // ---- 登录页 ----
    // URL: /login
    Volt::route('login', 'pages.auth.login')
        ->name('login');

    // ---- 忘记密码 ----
    // URL: /forgot-password
    // 用户输入邮箱，系统发送密码重置链接
    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    // ---- 重置密码 ----
    // URL: /reset-password/{token}
    // {token} 是占位符，从邮件链接获取
    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

// ============ 已登录用户路由 ============
// 中间件 'auth' 表示: 只有已登录的人才能访问
Route::middleware('auth')->group(function () {

    // ---- 邮箱验证提示页 ----
    // URL: /verify-email
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    // ---- 邮箱验证链接 ----
    // URL: /verify-email/{id}/{hash}
    // 用户点击邮件中的链接时触发
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // ---- 确认密码 ----
    // URL: /confirm-password
    // 访问敏感操作前要求再输入一次密码
    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
});
?>
```

---

### 📄 routes/userRoles.php

```php
<?php
use Illuminate\Support\Facades\Route;

// ============ Admin 专用路由 ============
// 中间件:
//   'auth' - 必须已登录
//   'role:Admin' - 必须有 Admin 角色（Spatie Permission）
// 两个中间件都要通过才能访问
Route::middleware(['auth', 'role:Admin'])->group(function () {

    // ---- 用户角色管理页面 ----
    // URL: /user-roles
    // 功能: Admin 用户可以给其他用户分配/删除角色
    Route::view('user-roles', 'user-roles')
        ->name('user-roles');
});
?>
```

---

### 📄 app/Models/User.php

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;  // 可认证的用户
use Illuminate\Notifications\Notifiable;                 // 可接收通知
use Laravel\Sanctum\HasApiTokens;                        // API 令牌支持
use Spatie\Permission\Traits\HasRoles;                   // 角色/权限支持

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    //  ↑ 导入 traits，添加相应功能

    // ============ 可批量赋值的字段 ============
    // 可以通过 User::create([...]) 直接设置
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'phone',
        'email',
        'password',
    ];

    // ============ 隐藏字段 ============
    // 将模型转换为 JSON 时会隐藏这些字段
    // 目的: 防止敏感信息泄露
    protected $hidden = [
        'password',        // 不显示密码哈希
        'remember_token',  // 不显示记住登录令牌
    ];

    // ============ 字段类型转换 ============
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            //  ↑ 自动将 string 转换为 Carbon DateTime 对象
            
            'password' => 'hashed',
            //  ↑ 保存时自动 hash，对比时自动验证
        ];
    }

    // ============ 手动定义的关系 ============
    // HasRoles trait 已包含:
    // - roles()          - 获取用户的所有角色
    // - hasRole('Admin') - 检查是否有某个角色
}
?>
```

**使用例子：**
```php
// 创建用户
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'secret123',  // 自动hash
]);

// 给用户分配角色
$user->assignRole('Admin');

// 检查角色
if ($user->hasRole('Admin')) {
    // 这个用户是 Admin
}
```

---

### 📄 app/Models/Tester.php

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tester extends Model
{
    use HasFactory;

    // ============ 可批量赋值的字段 ============
    protected $fillable = [
        'customer_id',      // 外键: 属于哪个客户
        'model',            // 仪器型号, 如 "XT-2000"
        'serial_number',    // 序列号, 用于唯一识别
        'purchase_date',    // 购买日期
        'status',           // 状态: active(活跃), inactive(停用), maintenance(维护中)
        'location',         // 位置: 哪个办公室/工作台
        'notes',            // 备注字段
    ];

    // ============ 字段类型转换 ============
    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',  // 自动转换为 Date 对象
        ];
    }

    // ============ 关系1: 属于某个客户 ============
    // 定义: 一个 Tester 属于一个 TesterCustomer
    // 类型: BelongsTo (后向关系)
    public function customer(): BelongsTo
    {
        return $this->belongsTo(TesterCustomer::class, 'customer_id');
        //                                            ↑ 外键字段名
    }

    // ============ 关系2: 有多个配件 ============
    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class);
        // 一个 Tester 对应多个 Fixture
    }

    // ============ 关系3: 有多个维护计划 ============
    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    // ============ 关系4: 有多个校准计划 ============
    public function calibrationSchedules(): HasMany
    {
        return $this->hasMany(CalibrationSchedule::class);
    }

    // ============ 关系5: 有多个事件日志 ============
    public function eventLogs(): HasMany
    {
        return $this->hasMany(EventLog::class);
    }
}
?>
```

**使用例子：**
```php
// 获取仪器及其所有关联数据
$tester = Tester::with('fixtures', 'maintenanceSchedules', 'eventLogs')->find(1);

// 访问关联数据
echo $tester->customer->company_name;        // 客户公司名
echo $tester->fixtures->count();             // 配件数量

// 遍历关联数据
foreach ($tester->maintenanceSchedules as $schedule) {
    echo $schedule->scheduled_date;          // 维护日期
}
```

---

### 📄 app/Livewire/Pages/Dashboard/EventBox.php

```php
<?php
namespace App\Livewire\Pages\Dashboard;

use Livewire\Component;

class EventBox extends Component
{
    // ============ 组件属性 ============
    // 这些是从父视图传来的参数,也是响应式的
    public $title;      // 盒子标题, 如 "Active Issues"
    public $items = []; // 要显示的事件列表
    public $type;       // 筛选类型: 'all', 'issues', 'events'
    public $limit;      // 显示的最大项目数

    // ============ 组件挂载时执行 ============
    // mount() 方法在组件初始化时自动被调用
    public function mount($title = "All Events", $type = 'all', $limit = 4)
    {
        // 保存参数值
        $this->type = $type;
        $this->title = $title;
        $this->limit = $limit;

        // ============ ⚠️ 模拟数据 (假数据！) ============
        // TODO: 这里应该从数据库查询真实数据
        //       目前使用硬编码的模拟数据以演示布局
        $mockItems = [
            [
                'type' => 'issue',        // 问题类型
                'tester' => 'Tester 01',  // 仪器名称
                'date' => now()->addDays(2),  // 2天后
            ],
            [
                'type' => 'maintenance',
                'tester' => 'Tester 02',
                'date' => now()->addDays(7),
            ],
            // ... 更多模拟数据 ...
        ];

        // ============ 按类型筛选 ============
        if ($this->type === 'events') {
            // 显示: maintenance 和 calibration (不包括 issue)
            $filtered = array_filter(
                $mockItems,
                fn ($item) => $item['type'] !== 'issue'
            );
        } 
        elseif ($this->type === 'issues') {
            // 显示: 只有 issue
            $filtered = array_filter(
                $mockItems,
                fn ($item) => $item['type'] === 'issue'
            );
        }
        else {
            // 显示: 所有项目
            $filtered = $mockItems;
        }

        // 重新编号数组 (删除空键)
        $this->items = array_values($filtered);

        // ============ 按日期排序 ============
        // 按 date 字段的时间戳升序排列
        usort($this->items, function ($a, $b) {
            return $a['date']->timestamp <=> $b['date']->timestamp;
        });

        // ============ 限制显示数量 ============
        // 只保留前 $limit 条记录
        $this->items = array_slice($this->items, 0, $this->limit);
    }

    // ============ 渲染方法 ============
    // 告诉 Livewire 这个组件对应的视图文件
    public function render()
    {
        return view('livewire.pages.dashboard.event-box');
    }
}
?>
```

**工作流程：**
```
1. Dashboard 页面加载
   @livewire('pages.dashboard.event-box', ['title'=>'Active Issues', 'type'=>'issues'])

2. Livewire 创建 EventBox 实例
   - $title = 'Active Issues'
   - $type = 'issues'
   - $limit = 4 (默认)

3. 自动调用 mount()
   - 加载模拟数据
   - 筛选 type='issue' 的项目
   - 按日期排序
   - 限制为前4条

4. 调用 render()
   - 返回视图, $items 已填充

5. Blade 在视图中使用 $items
```

---

### 📄 app/Livewire/Pages/Dashboard/Calendar.php

```php
<?php
namespace App\Livewire\Pages\Dashboard;

use Livewire\Component;

class Calendar extends Component
{
    // ============ 组件属性 ============
    public $events = [];  // 存储日历事件数组

    // ============ 挂载时执行 ============
    public function mount()
    {
        // ============ ⚠️ 模拟事件数据 ============
        // 这些是硬编码的假数据, 应该从数据库查询
        $this->events = [
            [
                'id' => '1',
                'calendarId' => '1',
                'title' => 'Tester calibration',
                'description' => 'Calibration of tester description...',
                'type' => 'calibration',
                'start' => '2026-03-25T10:00:00',  // ISO 8601 格式
                'end' => '2026-03-25T11:00:00',
            ],
            [
                'id' => '2',
                'calendarId' => '1',
                'title' => 'Tester maintenance',
                'description' => 'Maintenance of tester...',
                'type' => 'maintenance',
                'start' => '2026-03-28T09:00:00',
                'end' => '2026-03-28T12:00:00',
            ],
            // ... 更多事件 ...
        ];

        // ============ 触发 JavaScript 事件 ============
        // dispatch() 发送事件给浏览器
        // JavaScript 会监听 'calendar-ready' 事件
        $this->dispatch('calendar-ready');
    }

    // ============ 渲染方法 ============
    public function render()
    {
        return view('livewire.pages.dashboard.calendar');
    }
}
?>
```

**事件触发流程：**
```
1. PHP   Calendar::mount() 执行
         $this->dispatch('calendar-ready')

2. PHP   render() 返回视图, 并将事件编码到 HTML
         <div id="calendar" data-events='@json($events)'>

3. HTML  浏览器接收包含事件 JSON 的 HTML

4. JS    document.addEventListener('calendar-ready', ...)
         监听事件被触发

5. JS    const events = JSON.parse(...)
         从 HTML 属性中提取 JSON

6. JS    new Calendar(calendarEl, {..., events: events})
         创建 FullCalendar 实例

7. JS    calendar.render()
         渲染日历到页面
```

---

### 📄 app/Livewire/Pages/Admin/EditUserRoles.php

```php
<?php
namespace App\Livewire\Pages\Admin;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use App\Models\User;

class EditUserRoles extends Component
{
    // ============ 组件属性 ============
    public $users;                  // 所有用户列表
    public $roles;                  // 所有角色列表
    public $selectedUserId = null;  // 当前选中的用户 ID
    public $selectedRoleName = null;// 当前选中的角色名
    public $selectedUser = null;    // 当前选中用户的完整数据

    // ============ 挂载时执行 ============
    public function mount()
    {
        // 加载所有用户, 并预加载其角色 (性能优化)
        // with('roles') = eager loading, 减少数据库查询次数
        $this->users = User::with('roles')->get();
        
        // 加载所有角色
        $this->roles = Role::all();
    }

    // ============ 用户选中时执行 ============
    // 当用户从下拉菜单选择一个用户时触发
    public function selectUser()
    {
        // 获取选中用户的完整信息及其角色
        $this->selectedUser = User::with('roles')->find($this->selectedUserId);
        
        // PHP 8 的"安全导航符"?->
        // 如果任何环节为 null, 立即返回 null (不报错)
        $this->selectedRoleName = $this->selectedUser?->roles?->first()?->name;
    }

    // ============ 更新用户角色 ============
    // 点击 "Update Role" 按钮时执行
    public function updateUserRole()
    {
        // 验证必要参数
        if (!$this->selectedUserId || !$this->selectedRoleName) {
            return;
        }

        // 获取用户对象
        $user = User::find($this->selectedUserId);

        if ($user) {
            // ============ syncRoles() - Spatie Permission 方法 ============
            // 作用: 同步用户的角色
            // 流程:
            //   1. 删除用户的所有旧角色
            //   2. 添加新角色
            //   3. 只保留传入的角色
            $user->syncRoles([$this->selectedRoleName]);

            // 刷新组件的数据
            $this->selectedUser = $user->load('roles');
            $this->users = User::with('roles')->get();
        }
    }

    // ============ 删除用户角色 ============
    // 点击 "Remove Role" 按钮时执行
    public function removeUserRole()
    {
        if (!$this->selectedUserId || !$this->selectedRoleName) {
            return;
        }

        $user = User::find($this->selectedUserId);
        if (!$user) {
            return;
        }

        // removeRole() = 从用户移除指定角色
        $user->removeRole($this->selectedRoleName);

        // 刷新数据
        $this->selectedUser = $user->load('roles');
        $this->users = User::with('roles')->get();
    }

    // ============ 渲染方法 ============
    public function render()
    {
        return view('livewire.pages.admin.edit-user-roles');
    }
}
?>
```

**Spatie Permission 常用方法：**
```php
// 获取角色
$user->roles;                              // 所有角色 (Collection)
$user->getRoleNames();                     // 角色名称 (Array)

// 检查角色
$user->hasRole('Admin');                   // bool
$user->hasAnyRole(['Admin', 'Manager']);   // bool
$user->hasAllRoles(['Admin', 'Manager']);  // bool

// 分配角色
$user->assignRole('Admin');                // 添加单个角色
$user->syncRoles(['Admin']);               // 替换所有角色为 [Admin]
$user->attachRoles(['Admin', 'Manager']);  // 添加多个角色

// 删除角色
$user->removeRole('Admin');                // 移除单个角色
$user->syncRoles([]);                      // 清除所有角色
```

---

## 路由与流程详解

### 🔄 完整登录流程

**场景：用户首次登录系统**

```
Step 1: 用户访问 /login
   ↓ 路由匹配 auth.php
   ↓ Volt::route('login', 'pages.auth.login')
   ↓ 检查 middleware 'guest'
   ↓ 用户未登录 ✓ 通过
   ↓ 加载 login.blade.php

Step 2: 显示登录表单
   ├─ <input wire:model="form.email" />
   ├─ <input wire:model="form.password" />
   └─ <button wire:submit="login">Log in</button>

Step 3: 用户输入邮箱和密码
   ├─ 键入 "admin@example.com"
   ├─ 键入 "12345678"
   └─ wire:model 自动在后台发送值到 PHP

Step 4: 用户点击 "Log in" 按钮
   ├─ 触发 wire:submit="login"
   ├─ Laravel 发送 AJAX 请求到服务器
   └─ 调用组件的 login() 方法

Step 5: login() 方法执行
   ├─ $this->validate()
   │  └─ 验证 $form->email 和 $form->password
   │     (检查: 不为空、邮箱格式、密码长度等)
   │
   ├─ $this->form->authenticate()
   │  └─ LoginForm.php 的 authenticate() 方法
   │
   └─ Auth::attempt(['email'=>..., 'password'=>...])
      ├─ Laravel 到 users 表查询该邮箱
      ├─ 找到后获取 password 字段 (已hash)
      ├─ 使用 bcrypt 对比输入的密码
      ├─ 匹配 ✓ 返回 true
      └─ 不匹配 ✗ 返回 false, 抛异常

Step 6: 验证成功
   ├─ Session::regenerate()
   │  └─ 重新生成 Session ID (防止 Session 固定攻击)
   │
   ├─ Laravel 在 Session 中存储:
   │  └─ $_SESSION['_auth_user_id'] = 1 (用户 ID)
   │
   └─ $this->redirectIntended(default: route('dashboard'))
      └─ 重定向到 /dashboard

Step 7: 用户看到 Dashboard
   ├─ 浏览器向 /dashboard 发送请求
   ├─ 路由检查 middleware ['auth', 'verified']
   ├─ 'auth' 中间件:
   │  └─ 从 Session 读取 _auth_user_id
   │  └─ auth()->user() 自动加载用户
   ├─ 'verified' 中间件:
   │  └─ 检查 email_verified_at 是否为 null
   └─ 都通过 ✓ 加载 dashboard.blade.php

Step 8: 后续请求
   ├─ 用户在 Dashboard 进行操作
   ├─ Laravel 自动从 Session 识别用户
   ├─ auth()->user() 返回用户对象
   └─ 用户保持登录状态 (直到退出或 Session 过期)
```

---

### 🔄 完整注册流程

**场景：新用户注册账户**

```
Step 1: 用户访问 /register
   ↓ 路由: Volt::route('register', 'pages.auth.register')
   ↓ 检查 middleware 'guest'
   ↓ 用户未登录 ✓ 通过
   ↓ 加载 register.blade.php

Step 2: 显示注册表单
   ├─ <input wire:model="first_name" />
   ├─ <input wire:model="last_name" />
   ├─ <input wire:model="phone" />
   ├─ <input wire:model="email" />
   ├─ <input wire:model="password" type="password" />
   ├─ <input wire:model="password_confirmation" type="password" />
   └─ <button wire:submit="register">Register</button>

Step 3: 用户填写表单
   ├─ first_name = "John"
   ├─ last_name = "Doe"
   ├─ phone = "555-1234"
   ├─ email = "john@example.com"
   ├─ password = "SecurePass123"
   └─ password_confirmation = "SecurePass123"

Step 4: 用户点击 "Register"
   ├─ 触发 wire:submit="register"
   └─ 调用 register() 方法

Step 5: register() 方法执行
   ├─ $this->validate([...]
   │  ├─ first_name: required, string, max 100
   │  ├─ last_name: required, string, max 100
   │  ├─ phone: required, string, max 50, 只能包含数字/+-()空格
   │  ├─ email: required, unique 在 users 表中
   │  │   (检查是否已有相同邮箱)
   │  └─ password: required, confirmed
   │     (password_confirmation 必须与 password 相同)
   │
   ├─ $validated['name'] = first_name + last_name
   │  └─ 组合为全名, 如 "John Doe"
   │
   ├─ $validated['password'] = Hash::make(...)
   │  └─ 用 bcrypt 加密密码
   │
   ├─ User::create($validated)
   │  ├─ 插入到 users 表
   │  └─ 自动设置 created_at 和 updated_at
   │
   ├─ event(new Registered($user))
   │  └─ 触发"已注册"事件 (用于邮件验证等)
   │
   └─ Auth::login($user)
      ├─ 在 Session 中保存用户 ID
      └─ 相当于自动登录

Step 6: 赋予初始角色
   └─ 新用户通常被分配 'Guest' 角色
      (在 DatabaseSeeder.php 中配置)

Step 7: 重定向到 Dashboard
   ├─ $this->redirect(route('dashboard'))
   └─ 用户已登录, 可直接访问

Step 8: 邮箱验证
   ├─ 系统发送邮件到用户邮箱
   ├─ 用户点击邮件中的验证链接
   ├─ 更新 email_verified_at 为当前时间
   └─ /dashboard 不再要求邮箱验证
      (因为 email_verified_at 已设置)
```

---

### 🔄 Dashboard 加载完整流程

**场景：用户已登录, 访问 Dashboard**

```
Step 1: 用户在浏览器地址栏输入 /dashboard

Step 2: Laravel 路由匹配
   ├─ routes/web.php
   ├─ Route::view('dashboard', 'dashboard')
   │  ->middleware(['auth', 'verified'])
   └─ 开始中间件检查

Step 3: 'auth' 中间件
   ├─ 检查 $_SESSION['_auth_user_id'] 是否存在
   ├─ 存在 ✓ 加载用户到 auth()->user()
   └─ 不存在 ✗ 重定向到 /login

Step 4: 'verified' 中间件
   ├─ 获取当前用户 auth()->user()
   ├─ 检查 $user->email_verified_at 是否为 null
   ├─ 不为 null ✓ 通过
   └─ 为 null ✗ 重定向到 /verify-email

Step 5: 加载 dashboard.blade.php
   ├─ Blade 模板开始渲染
   └─ 遇到 Livewire 组件声明

Step 6: EventBox 第一个实例 (Active Issues)
   ├─ @livewire('pages.dashboard.event-box', [
   │    'title' => 'Active Issues',
   │    'type' => 'issues'
   │  ])
   │
   ├─ Livewire 创建 EventBox 实例
   │
   ├─ 设置属性:
   │  ├─ $title = 'Active Issues'
   │  ├─ $type = 'issues'
   │  └─ $limit = 4 (默认)
   │
   ├─ 调用 mount()
   │  ├─ 加载模拟数据 $mockItems (10条)
   │  ├─ 筛选: 只保留 type='issue' 的项目
   │  ├─ 排序: 按 date 升序
   │  └─ 限制: 只保留前4条
   │
   └─ 调用 render()
      └─ 返回 event-box.blade.php 视图

Step 7: EventBox 第二个实例 (Upcoming Events)
   ├─ @livewire('pages.dashboard.event-box', [
   │    'title' => 'Upcoming Events',
   │    'type' => 'events'
   │  ])
   │
   ├─ 类似流程, 但:
   │  └─ type='events' 表示: 排除 issue, 显示 maintenance/calibration
   │
   └─ 返回视图

Step 8: Calendar 组件
   ├─ @livewire('pages.dashboard.calendar')
   │
   ├─ Livewire 创建 Calendar 实例
   │
   ├─ 调用 mount()
   │  ├─ $this->events = [ ... 9条硬编码事件 ... ]
   │  └─ $this->dispatch('calendar-ready')
   │     └─ 发送 JavaScript 事件给浏览器
   │
   └─ 调用 render()
      ├─ 返回 calendar.blade.php
      ├─ <div id="calendar" data-events='@json($events)'>
      │  └─ 把 $events 数组转换为 JSON 字符串
      │  └─ 嵌入到 HTML 属性中
      └─ 浏览器接收包含 JSON 的 HTML

Step 9: JavaScript 初始化
   ├─ resources/js/app.js 在页面加载时执行
   ├─ 导入 resources/js/calendar.js
   │
   └─ calendar.js 中:
      ├─ document.addEventListener('calendar-ready', function() {
      │
      │  Step 1: 获取日历容器
      │  const calendarEl = document.getElementById('calendar')
      │
      │  Step 2: 从 HTML 属性提取 JSON
      │  const events = JSON.parse(calendarEl.dataset.events)
      │  // events = [{id:1, title:..., start:..., type:...}, ...]
      │
      │  Step 3: 配置 FullCalendar
      │  const config = {
      │    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
      │    initialView: 'dayGridMonth',
      │    events: events,  // ← 使用从 PHP 来的事件
      │    eventClassNames: function(arg) {
      │      return [arg.event.extendedProps.type];
      │      // type='calibration' → class="calibration"
      │      // 不同类型的事件显示不同颜色
      │    }
      │  }
      │
      │  Step 4: 初始化 FullCalendar
      │  const calendar = new Calendar(calendarEl, config)
      │
      │  Step 5: 渲染到页面
      │  calendar.render()
      │})

Step 10: 页面完全加载
   ├─ 两个 EventBox 显示事件列表 (左右两列)
   ├─ Calendar 显示日历视图 (下方)
   └─ JavaScript 事件处理已就位
      (点击事件、悬停等相应功能)
```

---

## Livewire与前端JS协作

### 📡 三种通信方式

#### 1️⃣ 数据绑定 (wire:model)

**最常见的用法：**

```blade
<input wire:model="email" />
<input wire:model="password" type="password" />

<!-- 流程：
1. 用户输入内容到 input
2. 浏览器触发 input change 事件
3. Livewire 自动发送 AJAX 请求
   { email: "...", password: "..." }
4. PHP 属性收到数据并更新
   $this->email = "..."
   $this->password = "..."
5. 组件自动重新渲染
6. 返回新的 HTML 片段
7. Livewire 检测变化并更新 DOM
8. 用户看到实时更新

（完全自动，无需编写 JS 代码！）
-->
```

#### 2️⃣ 事件处理 (wire:click, wire:submit, wire:change)

```blade
<!-- 表单提交 -->
<form wire:submit="register">
  ...
  <button type="submit">Register</button>
</form>
<!-- 点击按钮时调用 register() 方法 -->

<!-- 按钮点击 -->
<button wire:click="deleteUser">Delete</button>
<!-- 点击时调用 deleteUser() 方法 -->

<!-- 下拉菜单改变 -->
<select wire:model="selectedUserId" wire:change="selectUser">
  @foreach($users as $user)
    <option value="{{ $user->id }}">{{ $user->name }}</option>
  @endforeach
</select>
<!-- 选择改变时调用 selectUser() 方法 -->

<!-- 防抖 (debounce) 500ms 后发送 -->
<input wire:model.debounce-500ms="searchQuery" />

<!-- 节流 (throttle) 1s 最多发送一次 -->
<input wire:model.throttle-1000ms="filterText" />
```

#### 3️⃣ PHP 驱动 JavaScript (dispatch)

```php
// PHP 端
class Calendar extends Component {
    public function mount() {
        $this->events = [...];
        $this->dispatch('calendar-ready');  // 发送事件
    }
}
```

```javascript
// JavaScript 端
document.addEventListener('calendar-ready', function() {
    // 当 PHP 触发 dispatch('calendar-ready') 时执行
    const calendar = new FullCalendar.Calendar(...)
    calendar.render()
});
```

### 🔗 Dashboard 中的完整协作例子

**EventBox 组件与 Livewire 通信：**

```blade
<!-- 视图: event-box.blade.php -->

<div class="bg-white rounded-lg shadow">
    <h3>{{ $title }}</h3>
    
    <ul>
        <!-- $items 由 PHP mount() 填充 -->
        @forelse($items as $item)
            <li class="border rounded p-3">
                <div>{{ $item['tester'] }} - {{ $item['type'] }}</div>
                <div>{{ $item['date']->format('Y-m-d') }}</div>
            </li>
        @empty
            <li>No data</li>
        @endforelse
    </ul>
</div>
```

**流程图：**

```
1. Dashboard 视图加载
   @livewire('pages.dashboard.event-box', 
     ['title'=>'Active Issues', 'type'=>'issues']
   )

2. Livewire 创建组件并调用 mount()
   mount(title='Active Issues', type='issues', limit=4)
   ├─ 加载模拟数据 $mockItems
   ├─ 筛选 type='issue' 的项目
   ├─ 排序和限制
   └─ 填充 $this->items

3. render() 返回视图
   视图中遍历 $items
   @foreach($items as $item)
     <li>{{ $item['tester'] }}</li>
   @endforeach

4. Blade 编译为 HTML
   <ul>
     <li>Tester 01</li>
     <li>Tester 02</li>
   </ul>

5. Livewire 渲染并返回给浏览器

6. 浏览器显示事件列表
```

### 💡 Calendar 和 JavaScript 协作详解

```
┌────────────────────────────────────┐
│   1. PHP: Calendar 组件挂载        │
│   public function mount() {        │
│     $this->events = [...];         │
│     $this->dispatch('calendar-ready');
│   }                                │
└────────────────────────────────────┘
           ↓
┌────────────────────────────────────┐
│   2. Blade: 渲染视图               │
│   <div id="calendar"               │
│        data-events='@json($events)'>
│   </div>                           │
│                                    │
│   输出：                           │
│   <div id="calendar"               │
│        data-events='[{"id":"1"...}]'>
│   </div>                           │
└────────────────────────────────────┘
           ↓
┌────────────────────────────────────┐
│   3. JavaScript: 监听事件          │
│   document.addEventListener(       │
│     'calendar-ready', function() { │
│       const calendarEl =           │
│         document.getElementById('calendar')
│       const events =               │
│         JSON.parse(                │
│           calendarEl.dataset.events
│         )                          │
│       // events = [{id:1, ...}, ...]
│     }                              │
│   )                                │
└────────────────────────────────────┘
           ↓
┌────────────────────────────────────┐
│   4. JavaScript: 初始化库          │
│   const calendar = new Calendar({  │
│     plugins: [...],                │
│     events: events,                │
│     ...                            │
│   })                               │
│   calendar.render()                │
└────────────────────────────────────┘
           ↓
┌────────────────────────────────────┐
│   5. 用户看到日历！                │
│   ┌──────────────────────────────┐│
│   │ March 2026                   ││
│   │ Sun Mon Tue Wed Thu Fri Sat  ││
│   │ ... 25(Calib) ... 28(Maint)  ││
│   └──────────────────────────────┘│
└────────────────────────────────────┘
```

---

## 数据库与业务模型

### 📊 数据库ER图（完整关系）

```
┌──────────────────┐               ┌──────────────────┐
│      Users       │               │      Roles       │
├──────────────────┤     Many-Many ├──────────────────┤
│ id (PK)          │───────────────│ id (PK)          │
│ email (Unique)   │               │ name             │
│ password         │               │ guard_name       │
│ name             │               └──────────────────┘
│ first_name       │                 (via model_has_roles)
│ last_name        │
│ phone            │
│ created_at       │
│ updated_at       │
└──────────────────┘


┌───────────────────────┐
│  TesterCustomers      │
├───────────────────────┤
│ id (PK)               │
│ company_name (Unique) │
│ address               │
│ contact_person        │
│ phone                 │
│ email                 │
│ created_at            │
│ updated_at            │
└───────────────┬───────┘
                │ One-To-Many
                ↓
┌───────────────────────┐
│      Testers          │
├───────────────────────┤
│ id (PK)               │
│ customer_id (FK) ─────→ TesterCustomer
│ model                 │
│ serial_number (Unique)│
│ purchase_date         │
│ status (enum)         │
│ location              │
│ notes                 │
│ created_at            │
│ updated_at            │
└───────────────┬───────┘
                │
        ┌───────┴────────┬──────────────┬──────────────┐
        │                │              │              │
  One-To-Many        One-To-Many    One-To-Many    One-To-Many
        │                │              │              │
        ↓                ↓              ↓              ↓
  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
  │  Fixtures    │ │Maintenance   │ │Calibration   │ │ EventLogs    │
  ├──────────────┤ │Schedules     │ │Schedules     │ ├──────────────┤
  │ id (PK)      │ ├──────────────┤ ├──────────────┤ │ id (PK)      │
  │ tester_id    │ │ id (PK)      │ │ id (PK)      │ │ tester_id    │
  │ name         │ │ tester_id    │ │ tester_id    │ │ type (enum)  │
  │ serial_numb  │ │ scheduled_d  │ │ scheduled_d  │ │ event_date   │
  │ status       │ │ status       │ │ status       │ │ description  │
  │ location     │ │ procedure    │ │ procedure    │ │ performed_by │
  │ notes        │ │ completed_d  │ │ completed_d  │ │ metadata     │
  │              │ │ performed_by │ │ performed_by │ │              │
  └──────────────┘ │ notes        │ │ notes        │ └──────────────┘
                   └──────────────┘ └──────────────┘


┌──────────────────┐
│   SpareParts     │
├──────────────────┤
│ id (PK)          │
│ name             │
│ part_number      │
│ quantity_in_stk  │
│ unit_cost        │
│ supplier         │
│ notes            │
│ created_at       │
│ updated_at       │
└──────────────────┘
(独立表，暂未与其他表关联)
```

### 🗂️ 表结构详解

#### users 表

```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100),
    first_name VARCHAR(100),        -- 额外字段
    last_name VARCHAR(100),         -- 额外字段
    phone VARCHAR(50),              -- 额外字段
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**关键字段：**
- `email` - 邮箱，唯一索引
- `password` - 密码哈希值（使用 bcrypt）
- `email_verified_at` - 邮箱验证时间戳
- `first_name`, `last_name`, `phone` - 用户资料

#### tester_customers 表

```sql
CREATE TABLE tester_customers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_name VARCHAR(255) UNIQUE NOT NULL,
    address VARCHAR(255),
    contact_person VARCHAR(255),
    phone VARCHAR(50),
    email VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### testers 表

```sql
CREATE TABLE testers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT NOT NULL,     -- 链接到 tester_customers
    model VARCHAR(100) NOT NULL,     -- 型号，如 XT-2000
    serial_number VARCHAR(100) UNIQUE NOT NULL,
    purchase_date DATE,
    status ENUM('active', 'inactive', 'maintenance'),
    location VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES tester_customers(id)
);
```

#### event_logs 表

```sql
CREATE TABLE event_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tester_id BIGINT NOT NULL,
    type ENUM('maintenance', 'calibration', 'issue', 'repair', 'other'),
    event_date DATETIME NOT NULL,
    description TEXT,
    performed_by VARCHAR(255),
    metadata JSON,                   -- 额外数据，格式为 JSON
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (tester_id) REFERENCES testers(id)
);
```

### 🧠 模型关系使用示例

```php
// === 一对一 / 一对多 关系 ===

// 获取仪器的客户
$tester = Tester::find(1);
$customer = $tester->customer;
echo $customer->company_name;  // "Acme Corp"

// 获取客户的所有仪器
$customer = TesterCustomer::find(1);
$testers = $customer->testers;
foreach ($testers as $tester) {
    echo $tester->model;  // "XT-2000"
}

// === 链式关系查询 ===

// 获取某个客户的所有仪器的所有配件
$fixtures = TesterCustomer::find(1)
    ->testers()
    ->with('fixtures')
    ->get()
    ->pluck('fixtures')
    ->flatten();

// === 条件查询 ===

// 获取活跃的仪器
$activeTesters = Tester::where('status', 'active')->get();

// 获取某个客户的活跃仪器
$testers = Tester::where('customer_id', 1)
    ->where('status', 'active')
    ->get();

// === 使用 with() eager loading 优化查询 ===

// 👎 不好：N+1 查询问题
$testers = Tester::all();
foreach ($testers as $tester) {
    echo $tester->customer->company_name;  // 每次都查询一次
}
// 总共 1 + N 次查询

// 👍 好：使用 with() eager loading
$testers = Tester::with('customer')->get();
foreach ($t as $tester) {
    echo $tester->customer->company_name;  // 数据已加载
}
// 总共 2 次查询
```

---

## 权限系统与Seeder

### 🔐 Spatie Permission 权限系统

**表结构：**

```sql
-- 角色表
CREATE TABLE roles (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    guard_name VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 用户-角色关联表
CREATE TABLE model_has_roles (
    role_id BIGINT,
    model_id BIGINT,        -- user id
    model_type VARCHAR,      -- 'App\Models\User'
    PRIMARY KEY (role_id, model_id, model_type)
);

-- 权限表
CREATE TABLE permissions (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    guard_name VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 角色-权限关联表
CREATE TABLE role_has_permissions (
    permission_id BIGINT,
    role_id BIGINT,
    PRIMARY KEY (permission_id, role_id)
);
```

### 🌱 初始化数据 (Seeder)

**DatabaseSeeder.php：**

```php
<?php
namespace Database\Seeders;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. 调用 RoleSeeder 创建角色
        $this->call(RoleSeeder::class);

        // 2. 创建测试用户
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 3. 分配 Guest 角色
        $testUser->syncRoles(['Guest']);
    }
}
```

**RoleSeeder.php：**

```php
<?php
namespace Database\Seeders;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // ============ 创建角色 ============
        $roles = [
            'Admin',                    // 管理员 - 完全权限
            'Manager',                  // 经理 - 编辑权限
            'Maintenance Technician',   // 维护技师
            'Calibration Specialist',   // 校准专家
            'Guest',                    // 访客 - 只读
        ];

        // Role::findOrCreate() = 存在就用，不存在就创建
        foreach ($roles as $role) {
            Role::findOrCreate($role, 'web');
        }

        // ============ 创建默认用户 ============
        // 目的：便于开发和测试
        $defaultUsers = [
            [
                'email' => 'admin@example.com',
                'name' => 'Admin User',
                'password' => '12345678',
                'role' => 'Admin',
            ],
            [
                'email' => 'manager@example.com',
                'name' => 'Manager User',
                'password' => '12345678',
                'role' => 'Manager',
            ],
            [
                'email' => 'technician@example.com',
                'name' => 'Technician User',
                'password' => '12345678',
                'role' => 'Calibration Specialist',
            ],
            [
                'email' => 'guest@example.com',
                'name' => 'Guest User',
                'password' => '12345678',
                'role' => 'Guest',
            ],
        ];

        foreach ($defaultUsers as $entry) {
            // 创建或获取用户
            $user = User::firstOrCreate(
                ['email' => $entry['email']],
                [
                    'name' => $entry['name'],
                    'password' => Hash::make($entry['password']),  // 加密
                    'email_verified_at' => now(),                  // 标记已验证
                ]
            );

            // 分配角色
            $user->syncRoles([$entry['role']]);
        }
    }
}
```

### 📋 默认用户列表

```
Email                      | Password    | Role
---------------------------|-------------|---------------------------
admin@example.com          | 12345678    | Admin
manager@example.com        | 12345678    | Manager
technician@example.com     | 12345678    | Calibration Specialist
guest@example.com          | 12345678    | Guest
test@example.com           | password123 | Guest
```

**⚠️ 仅供开发使用！** 生产环境不应包含默认密码。

### 🔒 角色权限检查

**在 Policy 中使用（如 TesterPolicy.php）：**

```php
class TesterPolicy extends BasePolicy
{
    // 检查用户是否能创建仪器
    public function create(User $user): bool
    {
        return $this->hasAnyRole($user, ['admin', 'manager']);
        // Admin 和 Manager 可以创建
    }

    // 检查用户是否能删除仪器
    public function delete(User $user, Tester $tester): bool
    {
        return $this->isAdmin($user);
        // 只有 Admin 可以删除
    }
}
```

**在控制器中使用：**

```php
class TesterController extends Controller
{
    public function store(Request $request)
    {
        // 方式 1：自动授权
        $this->authorize('create', Tester::class);
        // 如果失败 → 返回 403 Forbidden

        // 方式 2：检查权限
        if ($request->user()->can('create', Tester::class)) {
            // 允许创建
        } else {
            // 禁止创建
        }
    }
}
```

**在 Blade 视图中使用：**

```blade
<!-- 只有有权限的用户才看到 -->
@can('create', $tester)
    <button wire:click="edit">Edit</button>
@endcan

@cannot('delete', $tester)
    <!-- 没有删除权限的用户看到禁用状态 -->
    <button disabled>Delete</button>
@endcannot
```

### 🧪 运行 Seeder

```bash
# 执行所有迁移 + 所有 Seeders
php artisan migrate:fresh --seed

# 只执行 Seeder (不重新迁移)
php artisan db:seed

# 执行特定 Seeder
php artisan db:seed --class=RoleSeeder
```

### 检查数据的 SQL

```sql
-- 查看所有角色
SELECT * FROM roles;

-- 查看用户及其角色
SELECT u.id, u.name, u.email, GROUP_CONCAT(r.name) as roles
FROM users u
LEFT JOIN model_has_roles mhr ON u.id = mhr.model_id
LEFT JOIN roles r ON mhr.role_id = r.id
GROUP BY u.id;

-- 查看某个用户的角色
SELECT r.name
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'admin@example.com';
```

---

## 模拟数据位置

### ⚠️ 项目中的模拟数据汇总

**模拟数据 = 硬编码在代码中的假数据，而不是from数据库查询的真实数据**

#### 1️⃣ EventBox 组件的模拟数据

**文件：** `app/Livewire/Pages/Dashboard/EventBox.php`

**位置：** `mount()` 方法，第 17-56 行

**代码：**
```php
// 注意这个 TODO 注释
// TODO: get db data

$mockItems = [
    [
        'type' => 'issue',
        'tester' => 'Tester 01',
        'date' => now()->addDays(2),
    ],
    [
        'type' => 'maintenance',
        'tester' => 'Tester 02',
        'date' => now()->addDays(7),
    ],
    // ... 更多硬编码项目 ...
];
```

**问题：**
- 数据完全硬编码
- 没有连接数据库
- EventLog 表没有被使用

**应改为：**
```php
// 从数据库查询真实事件
$query = EventLog::where('event_date', '>=', now());

if ($this->type === 'issues') {
    $query->where('type', 'issue');
} elseif ($this->type === 'events') {
    $query->whereIn('type', ['maintenance', 'calibration']);
}

$this->items = $query
    ->orderBy('event_date')
    ->limit($this->limit)
    ->get()
    ->map(function($event) {
        return [
            'type' => $event->type,
            'tester' => $event->tester->model,
            'date' => $event->event_date,
        ];
    })
    ->toArray();
```

---

#### 2️⃣ Calendar 组件的模拟数据

**文件：** `app/Livewire/Pages/Dashboard/Calendar.php`

**位置：** `mount()` 方法，第 13-73 行

**代码：**
```php
$this->events = [
    [
        'id' => '1',
        'title' => 'Tester calibration',
        'type' => 'calibration',
        'start' => '2026-03-25T10:00:00',  // 固定日期！
        'end' => '2026-03-25T11:00:00',
    ],
    // ... 9条硬编码事件 ...
];
```

**问题：**
- 所有日期是固定的（2026年3月25日等）
- 不会根据当前日期变化
- MaintenanceSchedule、CalibrationSchedule 表没有被使用

**应改为：**
```php
// 查询即将进行的维护和校准
$maintenances = MaintenanceSchedule::where('scheduled_date', '>=', now())
    ->take(10)
    ->get()
    ->map(fn($m) => [
        'id' => 'M-' . $m->id,
        'title' => 'Maintenance: ' . $m->tester->model,
        'start' => $m->scheduled_date->format('Y-m-dT09:00:00'),
        'end' => $m->scheduled_date->format('Y-m-dT12:00:00'),
        'type' => 'maintenance',
    ]);

$calibrations = CalibrationSchedule::where('scheduled_date', '>=', now())
    ->take(10)
    ->get()
    ->map(fn($c) => [
        'id' => 'C-' . $c->id,
        'title' => 'Calibration: ' . $c->tester->model,
        'start' => $c->scheduled_date->format('Y-m-dT10:00:00'),
        'end' => $c->scheduled_date->format('Y-m-dT11:00:00'),
        'type' => 'calibration',
    ]);

$this->events = $maintenances->merge($calibrations)->toArray();
```

---

### 📊 模拟数据影响分析

| 组件 | 影响 | 当前表现 | 真实表现 |
|------|------|---------|---------|
| EventBox | 列表数据 | 始终显示10个固定项目 | 应显示数据库中的真实事件 |
| Calendar | 日历数据 | 始终显示2026年3月的事件 | 应显示当前月和未来的事件 |
| - | 排序 | 按硬编码的顺序 | 应按real日期排序 |
| - | 实时性 | 静态，永不改变 | 应动态更新 |

### ✅ 检查清单

**要识别所有模拟数据，检查以下内容：**

```
☐ 搜索 "TODO" 注释
☐ 寻找硬编码的数组 ([ ... ])
☐ 查看是否有 "mock" 或 "fake" 变量名
☐ 检查是否使用了 Model::query()
☐ 验证 $this->dispatch() 的数据来源
☐ 查看 Blade 视图中的数据是否来自 mount()
```

---

## 快速参考

### 常用命令

```bash
# 创建迁移
php artisan make:migration create_testers_table

# 执行迁移
php artisan migrate

# 回滚上次迁移
php artisan migrate:rollback

# 重新创建数据库并填充数据
php artisan migrate:fresh --seed

# 清空所有缓存
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 启动开发服务器
php artisan serve

# 前端开发模式（监视文件变化）
npm run dev

# 生产打包
npm run build
```

### 关键文件速查表

| 功能 | 主要文件 | 关键类/函数 |
|------|---------|-----------|
| 路由定义 | routes/web.php | Route::view(), Volt::route() |
| 用户身份 | app/Models/User.php | HasRoles trait |
| 仪器数据 | app/Models/Tester.php | customer(), fixtures() 等关系 |
| 事件显示 | app/Livewire/Pages/Dashboard/EventBox.php | mount(), render() |
| 日历显示 | app/Livewire/Pages/Dashboard/Calendar.php | dispatch('calendar-ready') |
| 角色管理 | app/Livewire/Pages/Admin/EditUserRoles.php | syncRoles(), removeRole() |
| 权限检查 | app/Policies/TesterPolicy.php | hasAnyRole(), isAdmin() |
| 初始数据 | database/seeders/RoleSeeder.php | Role::findOrCreate() |
| 数据库表 | database/migrations/*.php | Schema::create() |

### PHP 中常见模式

```php
// === 模型查询 ===

// 获取单个记录
$tester = Tester::find(1);
$tester = Tester::findOrFail(1);  // 不存在返回 404
$tester = Tester::where('serial_number', 'SN001')->first();

// 获取多条记录
$testers = Tester::all();
$testers = Tester::where('status', 'active')->get();
$testers = Tester::with('customer')->get();

// 创建新记录
$tester = Tester::create([...]);

// 更新记录
$tester->update(['status' => 'inactive']);

// 删除记录
$tester->delete();

// === 关系使用 ===

$tester = Tester::find(1);
$customer = $tester->customer;  // 一对一
$fixtures = $tester->fixtures;  // 一对多 (Collection)

// === 权限检查 ===

if ($user->can('create', Tester::class)) {
    // 有权限
}

if ($user->hasRole('Admin')) {
    // 是管理员
}

// === Livewire 响应式 ===

public $items = [];

public function mount() {
    $this->items = [...];  // 赋值时自动响应式
}

public function updateItem($id) {
    $this->items[0]['name'] = 'New Name';
    // 页面自动更新，不需要 render()
}
```

---

## 总结思维导图

```
Tester Register 项目
│
├─ 用户系统
│  ├─ 认证：login/register
│  ├─ 权限：Spatie Permission
│  └─ 角色：Admin/Manager/Technician/Guest
│
├─ 业务模型
│  ├─ TesterCustomer (客户)
│  ├─ Tester (仪器)
│  │  ├─ Fixture (配件)
│  │  ├─ MaintenanceSchedule (维护)
│  │  ├─ CalibrationSchedule (校准)
│  │  └─ EventLog (事件)
│  └─ SparePart (配件库存)
│
├─ 前端框架
│  ├─ Livewire：PHP 驱动交互
│  │  ├─ EventBox：显示事件列表
│  │  ├─ Calendar：显示日历 (🔴 模拟数据)
│  │  └─ EditUserRoles：管理角色
│  │
│  ├─ Blade：模板引擎
│  ├─ Tailwind：样式框架
│  └─ FullCalendar：JS 日历库
│
├─ 路由
│  ├─ web.php：网页路由
│  ├─ auth.php：认证路由
│  └─ userRoles.php：角色管理路由
│
├─ 数据库
│  ├─ 迁移：定义表结构
│  ├─ 模型：PHP 类代表表
│  └─ Seeder：初始数据 (默认用户)
│
├─ 权限系统
│  ├─ Policy：授权规则 (TesterPolicy)
│  ├─ Middleware：检查权限
│  └─ Spatie Permission：角色/权限表
│
└─ ⚠️ 模拟数据位置
   ├─ EventBox.php 的 $mockItems
   └─ Calendar.php 的 $events
```

---

**本文档完成！希望这份详细的笔记能帮助你深入理解 Tester Register 项目。** 🎉

如有疑问，建议：
1. 查看代码中的注释
2. 运行 `php artisan tinker` 交互式探索
3. 查看 Laravel 官方文档
4. 调试日志查看执行流程
