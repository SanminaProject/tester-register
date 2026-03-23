# 🚀 API 快速开始指南 (5分钟快速上手)

**完成时间**: 约5-10分钟  
**难度**: 初级  
**前置条件**: PHP 8.2+, Composer, MySQL

---

## 📋 步骤1：启动服务器 (1分钟)

### 1.1 启动PHP开发服务器

```bash
cd e:\Github\tester-register
php artisan serve
```

**输出:**

```
INFO  Server running on [http://127.0.0.1:8000].

Press Ctrl+C to stop the server
```

✅ 服务器已启动！

---

## 👤 步骤2：创建测试用户 (2分钟)

### 2.1 进入Laravel Tinker (命令行交互式环境)

```bash
# 在新的终端窗口中运行
cd e:\Github\tester-register
php artisan tinker
```

### 2.2 创建角色

复制并粘贴以下代码到Tinker中：

```php
// 创建所有角色
\Spatie\Permission\Models\Role::create(['name' => 'admin']);
\Spatie\Permission\Models\Role::create(['name' => 'manager']);
\Spatie\Permission\Models\Role::create(['name' => 'technician']);
\Spatie\Permission\Models\Role::create(['name' => 'guest']);

echo "✅ Roles created successfully!";
```

### 2.3 创建测试用户

继续在Tinker中运行：

```php
// 创建用户
$user = \App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@test.com',
    'password' => bcrypt('password123'),
    'email_verified_at' => now(),
]);

// 分配Admin角色
$user->assignRole('admin');

echo "✅ User created: admin@test.com";
```

### 2.4 退出Tinker

```php
exit
```

✅ 用户创建完成！

---

## 🧪 步骤3：测试API (2-3分钟)

### 方法A: 使用Postman (推荐)

#### 3A.1 导入Postman集合

1. 打开 **Postman** 应用
2. 点击 **Import** 按钮
3. 选择 **File** 标签
4. 找到并选择: `Tester_Register_API.postman_collection.json`
5. 点击 **Import**

#### 3A.2 获取访问令牌

1. 在Postman中找到 **Auth** → **Login**
2. 点击 **Send** 按钮
3. 查看响应，复制 `data.access_token` 的值

**响应示例:**

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "access_token": "1|aBcdEfGhIjKlMnOpQrStUvWxYz123456",
        "token_type": "Bearer",
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@test.com",
            "roles": ["admin"]
        }
    },
    "code": 200
}
```

#### 3A.3 在Postman中设置全局变量

1. 点击 **Environment** 按钮 (右上角)
2. 点击 **Globals** 或创建新环境
3. 添加以下变量:
    - **变量名**: `token`
    - **初始值**: `YOUR_TOKEN`
    - **当前值**: 粘贴你复制的 `access_token`

4. 点击 **Save**

#### 3A.4 使用Token发送请求

1. 现在所有请求的 `Authorization` header 都会自动使用 `{{token}}`
2. 尝试 **Customers** → **Get All Customers**
3. 点击 **Send**

✅ 你应该能看到成功的响应！

---

### 方法B: 使用cURL命令

#### 3B.1 获取访问令牌

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password123"}'
```

**复制响应中的 `access_token` 值**

#### 3B.2 测试获取客户列表

```bash
curl -X GET http://localhost:8000/api/v1/customers \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN_HERE"
```

✅ 你应该能看到成功的响应！

---

### 方法C: 使用JavaScript (Node.js)

创建文件 `test.js`:

```javascript
const fetch = require("node-fetch");

async function testAPI() {
    try {
        // 1. 登录
        const loginRes = await fetch(
            "http://localhost:8000/api/v1/auth/login",
            {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    email: "admin@test.com",
                    password: "password123",
                }),
            },
        );

        const loginData = await loginRes.json();
        const token = loginData.data.access_token;
        console.log("✅ Login successful!");
        console.log("Access Token:", token);

        // 2. 获取客户列表
        const customersRes = await fetch(
            "http://localhost:8000/api/v1/customers",
            {
                method: "GET",
                headers: { Authorization: `Bearer ${token}` },
            },
        );

        const customersData = await customersRes.json();
        console.log("✅ Customers retrieved!");
        console.log("Customers:", customersData.data.items);
    } catch (error) {
        console.error("❌ Error:", error.message);
    }
}

testAPI();
```

运行:

```bash
node test.js
```

---

## 📊 完整API测试流程

### 1️⃣ 创建客户

```bash
curl -X POST http://localhost:8000/api/v1/customers \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "company_name": "Apple Inc",
    "address": "Cupertino, CA",
    "contact_person": "Steve Jobs",
    "phone": "+1-408-996-1010",
    "email": "contact@apple.com"
  }'
```

**响应:** 看到 `"success": true` 表示成功 ✅

**记下返回的 `customer_id`** (通常是 1)

### 2️⃣ 创建测试设备

```bash
curl -X POST http://localhost:8000/api/v1/testers \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "model": "iPhone Test 3000",
    "serial_number": "SN12345",
    "customer_id": 1,
    "purchase_date": "2025-01-15",
    "status": "active",
    "location": "Building A, Room 201"
  }'
```

**记下返回的 `tester_id`** (通常是 1)

### 3️⃣ 获取设备详情

```bash
curl -X GET http://localhost:8000/api/v1/testers/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4️⃣ 修改设备状态

```bash
curl -X PATCH http://localhost:8000/api/v1/testers/1/status \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"status": "maintenance"}'
```

### 5️⃣ 创建维护日程

```bash
curl -X POST http://localhost:8000/api/v1/maintenance-schedules \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "tester_id": 1,
    "scheduled_date": "2026-04-20",
    "procedure": "Regular maintenance",
    "notes": "Routine inspection"
  }'
```

### 6️⃣ 完成维护任务

```bash
curl -X POST http://localhost:8000/api/v1/maintenance-schedules/1/complete \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "completed_date": "2026-04-20",
    "performed_by": "John Smith",
    "notes": "Maintenance completed"
  }'
```

---

## ✨ 常见响应示例

### ✅ 成功响应 (200 OK)

```json
{
    "success": true,
    "message": "Customer created successfully",
    "data": {
        "id": 1,
        "company_name": "Apple Inc",
        "address": "Cupertino, CA",
        "contact_person": "Steve Jobs",
        "phone": "+1-408-996-1010",
        "email": "contact@apple.com",
        "created_at": "2026-03-23T15:30:00Z"
    },
    "code": 201
}
```

### ❌ 认证失败 (401 Unauthorized)

```json
{
    "success": false,
    "message": "Unauthenticated",
    "error": "UnauthorizedException",
    "code": 401
}
```

**解决方案**: 确保传递了有效的 Bearer Token

### ❌ 验证失败 (422 Unprocessable Entity)

```json
{
    "success": false,
    "message": "验证失败",
    "error": "ValidationException",
    "data": {
        "company_name": ["The company name field is required."]
    },
    "code": 422
}
```

**解决方案**: 检查你发送的数据是否符合API要求

---

## 🔧 常见问题解决

### Q1: "SQLSTATE Connection refused"

**原因**: 数据库没有启动  
**解决**:

```bash
# 确保MySQL/MariaDB运行
# 如果使用XAMPP，启动MySQL
```

### Q2: "Class not found"

**原因**: 没有运行迁移  
**解决**:

```bash
php artisan migrate
```

### Q3: "No application encryption key has been specified"

**原因**: 没有生成加密密钥  
**解决**:

```bash
php artisan key:generate
```

### Q4: "CORS error in browser"

**原因**: 跨域请求被阻止  
**解决**: 在前端请求中添加适当的CORS headers

---

## 📝 下一步建议

- [ ] 用Postman完整测试所有API
- [ ] 创建更多测试用户（不同角色）
- [ ] 尝试创建夹具、事件日志、备用件
- [ ] 测试权限系统（用其他角色尝试删除操作）
- [ ] 看看前面的 `API_DESIGN.md` 文件了解每个API的详细参数
- [ ] 查看 `API_IMPLEMENTATION_GUIDE.md` 了解架构细节

---

## 🎯 你现在拥有的完整API系统

✅ **认证系统** - 登录/登出  
✅ **客户管理** - 增删改查  
✅ **设备管理** - 增删改查 + 状态修改  
✅ **夹具管理** - 增删改查  
✅ **维护日程** - 增删改查 + 完成任务  
✅ **校准日程** - 增删改查 + 完成任务  
✅ **事件日志** - 增删查 (记录系统)  
✅ **备用件管理** - 增删改查 + 库存管理  
✅ **权限系统** - 基于角色的访问控制

---

**恭喜！你的API现在已经完全可用！** 🎉
