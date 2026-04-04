现在我将用计算机初学者友好的语言给你详细讲解这个项目。

## 📌 **项目简介（30秒快速理解）**

这是一个**用户角色管理系统**，用来帮助公司管理测试仪器的维护和校准事件。它是用 **Laravel**（一个 PHP web 框架）开发的，结合了 **Livewire**（让网页能实时和后端通信）和 **数据库**（存储用户和角色信息）。

---

## 🏗️ **项目的整体架构（就像一座大楼）**

想象网站像一座大楼：

```
┌─────────────────────────────────────┐
│   用户在浏览器看到的页面（前端）    │
│  欢迎页、登录页、仪表盘、管理页     │
└──────────────┬──────────────────────┘
               │ （HTTP请求）
┌──────────────▼──────────────────────┐
│   中间层：处理请求的逻辑部分        │
│  Livewire组件 + 路由 + 表单验证     │
└──────────────┬──────────────────────┘
               │ （读/写数据）
┌──────────────▼──────────────────────┐
│   底层：数据存储（数据库）           │
│  用户表、角色表、权限表              │
└─────────────────────────────────────┘
```

---

## 👥 **核心概念：用户、角色、权限**

### **什么是用户？**

- 就像你登录微信一样，每个注册的人都是一个用户
- 存储信息：姓名、邮箱、密码

### **什么是角色？**

- 角色是一个**身份标签**，决定了用户能做什么
- 这个项目有3种角色：
    - **Admin**（管理员）- 可以管理所有用户的角色
    - **Maintenance Technician**（维护技术员）- 负责设备维护
    - **Calibration Specialist**（校准专员）- 负责设备校准

### **权限的作用**

- 权限控制用户**能访问哪些页面**
- 例如：只有 Admin 才能进入"用户角色管理"页面

---

## 📄 **项目的主要页面（用户能看到的）**

### **1️⃣ 首页（Welcome Page）**

- **路由**：访问 `/`
- **作用**：展示项目介绍
- **你是否需要登录**：否（任何人都能看）

### **2️⃣ 登录/注册页面**

- **路由**：`/login` 和 `/register`
- **作用**：用户创建账户或登录
- **关键逻辑**在 LoginForm.php
    - ✅ 验证邮箱和密码格式
    - ✅ 防止频繁错误登录（最多5次）
    - ✅ 记住登录状态

### **3️⃣ 仪表盘（Dashboard）**

- **路由**：`/dashboard`
- **必须**：已登录 + 邮箱已验证
- **显示内容**：
    - 📅 **事件日历** - 显示所有维护和校准计划（使用 Calendar.php）
    - 📋 **事件列表** - 排序后显示最近要发生的事件（使用 EventBox.php）
    - 🔴 **问题列表** - 显示设备的问题

### **4️⃣ 个人资料页面（Profile）**

- **路由**：`/profile`
- **作用**：更新名字、下载数据、改密码、删除账户

### **5️⃣ 用户角色管理页面（仅Admin可访问）**

- **路由**：`/user-roles`
- **权限**：必须是 Admin 角色
- **作用**：
    - 选择一个用户
    - 给该用户分配角色（或者删除角色）
    - 逻辑在 EditUserRoles.php

---

## 🔑 **初始用户信息（第一次运行）**

项目启动时会运行一个"种子文件"（RoleSeeder.php），自动创建：

- **预设3个角色**：Admin、Maintenance Technician、Calibration Specialist
- **默认管理员账户**：
    - 邮箱：`admin@example.com`
    - 密码：`12345678`

---

## 🗄️ **数据库存了什么？**

数据库里有这些表格（就像 Excel 里的多个工作表）：

| 表名                   | 作用            | 关键字段                  |
| ---------------------- | --------------- | ------------------------- |
| `users`                | 存储用户信息    | id, name, email, password |
| `roles`                | 存储角色列表    | id, name                  |
| `permissions`          | 存储权限列表    | id, name                  |
| `model_has_roles`      | 用户-角色关系表 | 连接"用户"和"角色"        |
| `role_has_permissions` | 角色-权限关系表 | 连接"角色"和"权限"        |

这样做的好处：一个用户可以有多个角色，一个角色可以有多个权限。

---

## 🚀 **完整的用户注册流程（一步步）**

1. **用户打开** `/register` 页面
2. **输入**：名字、邮箱、密码
3. **点击**"注册"按钮 → 触发 JS 事件
4. **后端验证**：
    - 邮箱是否有效？
    - 密码是否足够长？
    - 邮箱是否已被注册？
5. **如果验证通过**：
    - 密码被"加密"后存入数据库
    - 用户账户创建成功
    - 系统自动登录
    - 跳转到 `/dashboard`
6. **如果验证失败**：
    - 页面显示错误信息

---

## 🔐 **完整的用户登录流程**

1. **用户打开** `/login` 页面
2. **输入**：邮箱和密码
3. **点击**"登录" → 触发 LoginForm.php 的 `authenticate()` 方法
4. **后端检查**：
    - 这个邮箱存在吗？
    - 密码正确吗？
    - 试错次数是否超过5次？（防止暴力破解）
5. **如果验证通过**：
    - 系统记住用户的登录状态
    - 跳转到 `/dashboard`
6. **如果失败**：显示错误提示（错误5次后会锁定15分钟）

---

## 👨‍💼 **Admin 管理用户角色的流程**

1. **Admin 打开** `/user-roles` 页面
2. **选择一个用户**：
    - 页面会获取该用户的当前角色
3. **选择新角色**（从下拉菜单）
4. **点击"更新"**：
    - EditUserRoles.php 的 `updateUserRole()` 方法执行
    - 该用户的旧角色被替换成新角色
    - 用户列表和选中用户的信息重新加载
5. **或者点击"删除"**：
    - 该用户的角色被清除

---

## ⚙️ **技术栈（用到的技术）**

| 层次         | 技术              | 作用                     |
| ------------ | ----------------- | ------------------------ |
| **后端框架** | Laravel           | 处理业务逻辑             |
| **实时交互** | Livewire          | 让网页能实时和后端通信   |
| **认证**     | Laravel Auth      | 处理登录和权限           |
| **角色权限** | Spatie Permission | 管理用户角色和权限       |
| **数据库**   | MySQL             | 存储数据                 |
| **前端框架** | Tailwind CSS      | 美化网页样式             |
| **打包工具** | Vite              | 压缩 JS/CSS 提高加载速度 |

---

## 🔄 **Livewire 如何工作？（核心概念）**

**传统网站模式**：用户点击按钮 → 整个页面刷新

**Livewire 模式**：用户点击按钮 → 只更新必要部分 → 页面不闪烁

例如，在仪表盘中：

1. `Calendar` 组件在 `mount()` 时加载模拟事件数据
2.  2. 发送 `calendar-ready` 事件给前端 JS
3. 前端 JS（calendar.js）监听事件，初始化日历插件
4. 用户交互时，Livewire 自动处理，不需要刷新页面

---

## 📁 **项目文件结构解读**

```
tester-register/
├── app/
│   ├── Models/User.php           ← 用户数据模型
│   ├── Livewire/
│   │   ├── Pages/Auth/           ← 认证页面（登录、注册）
│   │   ├── Pages/Admin/          ← Admin管理页面
│   │   └── Pages/Dashboard/      ← 仪表盘页面
│   └── Http/Controllers/         ← 传统控制器（很少用）
├── routes/
│   ├── web.php                   ← 主路由（首页、仪表盘、个人资料）
│   ├── auth.php                  ← 认证路由（登录、注册、重置密码）
│   └── userRoles.php             ← Admin管理路由
├── database/
│   ├── migrations/               ← 数据库表结构定义
│   └── seeders/                  ← 初始数据脚本
├── resources/
│   ├── views/                    ← HTML 模板（Blade语言）
│   └── js/, css/                 ← 前端资源
├── config/
│   └── permission.php            ← 权限系统配置
└── vendor/                       ← 依赖库（npm install 安装）
```

---

## ✅ **项目现在的功能**

- ✅ 用户注册和登录
- ✅ 邮箱验证
- ✅ 密码重置
- ✅ 仪表盘显示事件和日历（模拟数据）
- ✅ Admin 管理用户角色
- ✅ 个人资料管理

## ⏳ **项目下一步可以做什么**

- 🔲 连接真实数据库（现在用的是模拟数据）
- 🔲 添加设备（Tester）管理功能
- 🔲 添加事件创建/编辑功能
- 🔲 添加邮件通知（如：事件即将开始时发邮件提醒）
- 🔲 添加测试代码（TDD - 测试驱动开发）

---

## 🎯 **关键知识点总结**

1. **Laravel** = 写后端逻辑的框架
2. **Livewire** = 让网页实时更新而不用刷新
3. **路由** = URL 和代码的对应关系
4. **数据库** = 信息存储库
5. **Spatie Permission** = 角色和权限管理工具
6. **认证中间件** = 检查用户是否登录、是否有权限

有什么想深入了解的部分吗？比如登录流程、数据库结构或 Livewire 组件的工作方式？🤔

---

现在让我详细讲解这个项目的数据库结构。

---

## 📊 **数据库结构详解（初学者版）**

### **现在的情况**

这个项目的数据库分为**两层**：

1. **已实现的部分**：用户认证和角色管理
2. **计划的完整部分**：整个测试仪器管理系统

---

## 🔵 **第一部分：已实现的核心表**

### **1. `users` 表（用户表）**

存储所有用户的基本信息：

| 字段                | 类型         | 说明                          |
| ------------------- | ------------ | ----------------------------- |
| `id`                | INT          | 用户的唯一编号（主键）        |
| `name`              | VARCHAR(255) | 用户名称                      |
| `email`             | VARCHAR(255) | 邮箱地址（唯一，不能重复）    |
| `email_verified_at` | TIMESTAMP    | 邮箱验证时间（NULL = 未验证） |
| `password`          | VARCHAR(255) | 密码（加密存储）              |
| `remember_token`    | VARCHAR(100) | 记住登录状态的令牌            |
| `created_at`        | TIMESTAMP    | 账户创建时间                  |
| `updated_at`        | TIMESTAMP    | 最后更新时间                  |

**实际数据示例**：

```
id=1, name='Admin User', email='admin@example.com', email_verified_at='2026-03-15 10:20:00', password='$2y$12$...'
id=2, name='Test User', email='test@example.com', email_verified_at=NULL, password='$2y$12$...'
```

### **2. `password_reset_tokens` 表（密码重置令牌表）**

这是一个**临时表**，存储用户忘记密码时的重置链接：

| 字段         | 类型         | 说明                               |
| ------------ | ------------ | ---------------------------------- |
| `email`      | VARCHAR(255) | 用户邮箱（主键）                   |
| `token`      | VARCHAR(255) | 重置密码的令牌（加密的一长串字符） |
| `created_at` | TIMESTAMP    | 令牌创建时间                       |

**工作流程**：

1. 用户点击"忘记密码" → 输入邮箱
2. 系统生成一个令牌，存入此表
3. 发送邮件给用户，邮件里有链接：`https://site.com/reset-password/[token]`
4. 用户点击链接 → 验证令牌是否有效
5. 如果有效 → 允许设置新密码 → 删除令牌

### **3. `sessions` 表（会话表）**

记录用户的**登录状态**。当用户登录时，系统会在这个表中创建一条记录：

| 字段            | 类型         | 说明                              |
| --------------- | ------------ | --------------------------------- |
| `id`            | VARCHAR(255) | Session 的唯一编号（主键）        |
| `user_id`       | BIGINT       | 关联的用户 ID                     |
| `ip_address`    | VARCHAR(45)  | 用户的网络 IP 地址                |
| `user_agent`    | TEXT         | 用户的浏览器信息                  |
| `payload`       | LONGTEXT     | Session 数据（序列化的 PHP 数组） |
| `last_activity` | INT          | 最后活动时间戳                    |

**用途**：

- 如果用户已登录，系统可以根据 Session 识别用户身份
- Session 过期后会被自动删除

---

## 🟣 **第二部分：角色与权限表（Spatie Laravel Permission）**

这些表由 Spatie Permission 包自动生成。用来管理"谁能做什么"：

### **4. `roles` 表（角色表）**

| 字段         | 类型         | 说明                     |
| ------------ | ------------ | ------------------------ |
| `id`         | BIGINT       | 角色 ID                  |
| `name`       | VARCHAR(255) | 角色名称                 |
| `guard_name` | VARCHAR(255) | 看守类型（通常是 "web"） |
| `created_at` | TIMESTAMP    | 创建时间                 |
| `updated_at` | TIMESTAMP    | 更新时间                 |

**当前角色**：

```
id=1, name='Admin'
id=2, name='Maintenance Technician'
id=3, name='Calibration Specialist'
```

### **5. `permissions` 表（权限表）**

权限定义了具体的操作（目前还未使用）：

| 字段         | 说明                                          |
| ------------ | --------------------------------------------- |
| `id`         | 权限 ID                                       |
| `name`       | 权限名称（如 "view_testers", "edit_testers"） |
| `guard_name` | 看守类型                                      |

### **6. `model_has_roles` 表（用户-角色关系表）**

这个表是**桥梁**，连接用户和角色。一个用户可以有多个角色：

| 字段         | 说明                          |
| ------------ | ----------------------------- |
| `role_id`    | 角色 ID                       |
| `model_type` | 模型类型（"App\Models\User"） |
| `model_id`   | 用户 ID                       |

**实际数据示例**：

```
role_id=1, model_type='App\Models\User', model_id=1
// 表示：用户1是Admin
```

### **7. `model_has_permissions` 表（用户-权限关系表）**

类似上面，但连接用户和权限（目前未使用）

### **8. `role_has_permissions` 表（角色-权限关系表）**

连接角色和权限。例如：

```
permission_id=5, role_id=1
// 表示：Admin 角色拥有权限5
```

---

## 🟠 **第三部分：计划中的完整数据库（未实现）**

这些表定义在 schema.sql 中，代表完整的测试仪器管理系统：

### **关键业务表**

#### **1. `tester_customers` - 客户表**

```
客户ID → 客户名称（如 Nokia, Haltian）
```

#### **2. `tester_and_fixture_locations` - 位置表**

```
位置ID → 位置名称、描述、地址
```

存储测试仪器和夹具的物理位置。

#### **3. `testers` - 测试仪器表**（核心表）

这是项目的**心脏**，存储所有测试仪器的详细信息：

| 字段                    | 说明                           |
| ----------------------- | ------------------------------ |
| `tester_id`             | 仪器 ID                        |
| `tester_name`           | 仪器名称                       |
| `tester_description`    | 详细描述                       |
| `id_number_by_customer` | 客户给的编号                   |
| `operating_system`      | 操作系统                       |
| `tester_type`           | 仪器类型                       |
| `product_family`        | 产品系列                       |
| `manufacturer`          | 制造商                         |
| `implementation_date`   | 实施日期                       |
| `location_id`           | 物理位置（外键）               |
| `owner_id`              | 所有者/客户（外键）            |
| `tester_status`         | 状态：在用/停用/维护中（外键） |

#### **4. `tester_assets` - 仪器资产表**

```
资产编号 → 关联的仪器ID
```

每个仪器可能有多个资产号。

#### **5. `fixtures` - 夹具表**

```
夹具ID → 夹具名称、制造商、关联的仪器ID
```

仪器的附件。

---

### **维护和校准相关表**

#### **6. `tester_maintenance_procedures` - 维护程序定义表**

```
维护程序ID → 程序类型、间隔值、间隔单位、描述

例如：
id=1, type='Preventive Maintenance', interval_value=30, interval_unit='Days'
// 表示：预防性维护，每30天一次
```

#### **7. `tester_calibration_procedures` - 校准程序定义表**

```
校准程序ID → 程序类型、间隔值、间隔单位、描述

例如：
id=1, type='Standard Calibration', interval_value=90, interval_unit='Days'
// 表示：标准校准，每90天一次
```

#### **8. `tester_maintenance_schedules` - 维护计划表**

这个表**关联**仪器和维护程序，跟踪维护进度：

| 字段                          | 说明                  |
| ----------------------------- | --------------------- |
| `maintenance_schedule_id`     | 计划 ID               |
| `tester_id`                   | 哪个仪器              |
| `maintenance_id`              | 使用哪个维护程序      |
| `schedule_created_date`       | 计划创建时间          |
| `last_maintenance_date`       | 上次维护的日期        |
| `next_maintenance_due`        | 下次维护的计划日期 ⭐ |
| `maintenance_status`          | 状态：已排期/逾期     |
| `last_maintenance_by_user_id` | 上次谁做的            |
| `next_maintenance_by_user_id` | 下次谁来做            |

**实际例子**：

```
id=1, tester_id=5(仪器5), maintenance_id=1(30天维护程序)
schedule_created_date='2026-03-01', last_maintenance_date='2026-03-15'
next_maintenance_due='2026-04-14'  // 自动计算：2026-03-15 + 30天
maintenance_status='Scheduled'
last_maintenance_by_user_id=3, next_maintenance_by_user_id=4
```

#### **9. `tester_calibration_schedules` - 校准计划表**

完全类似维护计划表，但用于校准。

#### **10. `tester_event_logs` - 事件日志表**（超级重要！）

记录仪器发生的所有事件（维护、校准、问题等）：

| 字段                      | 说明                                                                    |
| ------------------------- | ----------------------------------------------------------------------- |
| `event_id`                | 事件 ID                                                                 |
| `event_date`              | 事件发生时间                                                            |
| `event_description`       | 事件描述                                                                |
| `tester_id`               | 涉及的仪器                                                              |
| `event_type`              | 事件类型：issue/maintenance/calibration/software_update/hardware_change |
| `created_by_user_id`      | 谁报告/记录的                                                           |
| `resolved_date`           | 如果是问题，何时解决                                                    |
| `resolved_by_user_id`     | 谁解决的                                                                |
| `issue_status`            | 问题状态：open/closed                                                   |
| `maintenance_schedule_id` | 关联的维护计划（如果是维护事件）                                        |
| `calibration_schedule_id` | 关联的校准计划（如果是校准事件）                                        |

**实际例子**：

```
// 记录一次维护事件
id=100, event_date='2026-03-15 14:30:00', event_type='maintenance'
tester_id=5, created_by_user_id=3, maintenance_schedule_id=1
event_description='Replaced worn belt and lubricated components'

// 记录一个仪器故障
id=101, event_date='2026-03-20 09:00:00', event_type='issue'
tester_id=5, created_by_user_id=2, issue_status='open'
event_description='Display screen flickering intermittently'
resolved_date=NULL, resolved_by_user_id=NULL
```

---

### **备用件和供应商表**

#### **11. `tester_spare_part_suppliers` - 供应商表**

```
供应商ID → 名称、联系人、邮箱、电话、地址
```

#### **12. `tester_spare_parts` - 备用件表**

```
备用件ID → 名称、制造商编号、库存数量、重新订购级别、上次订购日期、单价
关联：tester_id（属于哪个仪器）、supplier_id（供应商）
```

当库存 < 重新订购级别时，系统可以自动提醒。

---

### **其他支持表**

#### **13. `asset_statuses` - 资产状态表**

```
id=1, name='Active'（在用）
id=2, name='Inactive'（停用）
id=3, name='Maintenance'（维护中）
```

#### **14. `event_types` - 事件类型表**

```
id=1, name='issue'
id=2, name='maintenance'
id=3, name='calibration'
id=4, name='software_update'
id=5, name='hardware_change'
```

#### **15. `issue_statuses` - 问题状态表**

```
id=1, name='open'（打开）
id=2, name='closed'（已关闭）
```

#### **16. `procedure_interval_units` - 时间单位表**

```
id=1, name='Days'（天）
id=2, name='Weeks'（周）
id=3, name='Months'（月）
id=4, name='Years'（年）
```

#### **17. `schedule_statuses` - 计划状态表**

```
id=1, name='Scheduled'（已排期）
id=2, name='Overdue'（逾期）
```

#### **18. `data_change_logs` - 数据变更日志表**

```
谁在什么时间修改了什么，用于审计跟踪
```

#### **19. `user_tester_assignments` - 用户-仪器分配表**

这是一个**多对多关系表**，说明哪些用户负责哪些仪器：

| 字段        | 说明    |
| ----------- | ------- |
| `user_id`   | 用户 ID |
| `tester_id` | 仪器 ID |

**例子**：

```
user_id=3, tester_id=5  // 用户3负责仪器5
user_id=3, tester_id=7  // 用户3也负责仪器7
user_id=4, tester_id=5  // 用户4也负责仪器5
```

---

## 🔄 **表之间的关系图（简化版）**

```
┌─────────────┐
│   users     │◄──────────────────────────────┐
└──────┬──────┘                                  │
       │ (user_id)                              │
       ▼                                         │
┌─────────────────────┐                        │
│ user_tester_        │◄──┐                    │
│ assignments         │   │                    │
└──────────┬──────────┘   │                    │
           │              │                    │
           │(tester_id)   │(user_id)          │
           ▼              │                    │
    ┌──────────────┐     │                   │
    │   testers    │     │                   │
    └──────┬───────┘     │                   │
           │             │                   │
    ┌──────┴──────────────┴───────────────────┤
    │                                          │
    ▼(maintenance_schedule_id)                 ▼
┌─────────────────────┐          ┌─────────────────────┐
│ tester_maintenance_ │          │  tester_event_logs  │
│ schedules           │          │                     │
└──────────┬──────────┘          └──────────┬──────────┘
           │                                │
    (maintenance_id)                 (event_type_id)
           │                                │
           ▼                                ▼
┌─────────────────────┐          ┌─────────────────────┐
│ tester_maintenance_ │          │   event_types       │
│ procedures          │          │                     │
└─────────────────────┘          └─────────────────────┘
```

---

## ⚡ **高级概念：自动化和触发器**

### **MySQL EVENT（自动事件）**

在 events.sql 中定义了一个**每天自动运行的任务**：

```sql
CREATE EVENT update_next_service_dates
ON SCHEDULE EVERY 1 DAY
```

这个事件做了什么？

1. **自动计算下次维护日期**：

    ```
    如果 最后维护日期 = 2026-03-15
    且  维护间隔 = 30 天
    那么 下次维护应该 = 2026-04-14
    ```

2. **自动标记逾期**：

    ```
    如果 下次维护日期 < 现在时间
    且  当前状态 = 'Scheduled'
    那么 改为 'Overdue'（逾期）
    ```

3. **对校准也做同样的事**

**好处**：不需要人工手动修改日期，系统自动更新！

### **MySQL TRIGGER（触发器）**

在 triggers.sql 中：

当**插入一条新事件日志**时（比如记录了一次维护），**自动更新相关的计划表**：

```sql
CREATE TRIGGER trigger_after_insert_maintenance_or_calibration_event
AFTER INSERT ON tester_event_logs
```

例如：

```
维护技术员完成了一次维护，写入 tester_event_logs
↓（触发器自动触发）
更新 tester_maintenance_schedules：
  - last_maintenance_date = 新的维护日期
  - last_maintenance_by_user_id = 谁做的
  - maintenance_status = 重新设为 'Scheduled'
```

---

## 📊 **ER 图解读**

你看到的 ER 图显示了所有表和它们的关系。关键要点：

- **实线箭头**（─→）= 强制关系（外键）
- **虚线箭头**（┈→）= 可选关系（可能是 NULL）
- 表格顶部的**大写字段**是主键（Primary Key）

---

## 🎯 **整体数据流（一个实际场景）**

### **场景：维护技术员完成仪器维护**

```
1. 维护技术员登录系统
   ↓ 检查 users + model_has_roles 表

2. Dashboard 显示他负责的仪器
   ↓ 查询 user_tester_assignments 表

3. 仪器5 显示"下次维护：2026-04-14"
   ↓ 来自 tester_maintenance_schedules 表

4. 技术员完成维护 → 记录事件
   ↓ 插入 tester_event_logs 表

5. 触发器自动触发！
   ↓ 更新 tester_maintenance_schedules
   ↓ last_maintenance_date = 今天
   ↓ next_maintenance_due = 今天 + 30天

6. 每天凌晨 EVENT 触发
   ↓ 检查所有计划的下次日期
   ↓ 如果逾期，改为 'Overdue' 状态

7. Dashboard 显示更新后的信息
   ↓ 管理员和技术员可以看到最新进度
```

---

## 📝 **当前项目 vs 完整计划**

| 功能          | 现在实现                   | 计划中            |
| ------------- | -------------------------- | ----------------- |
| 用户注册/登录 | ✅                         | ✅（已实现）      |
| 邮箱验证      | ✅                         | ✅（已实现）      |
| 角色管理      | ✅                         | ✅（已实现）      |
| 权限系统      | 🟡（框架已装，权限未定义） | ✅                |
| 仪器管理      | ❌（只有模拟数据）         | 计划中            |
| 维护/校准计划 | ❌（只有模拟数据）         | 计划中            |
| 事件日志      | ❌（只有模拟数据）         | 计划中            |
| 自动计算日期  | ❌                         | 计划中（EVENT）   |
| 自动状态更新  | ❌                         | 计划中（TRIGGER） |

---

## 🔑 **核心要点总结**

1. **用户表** = 存储登录信息
2. **角色权限表** = 控制谁能做什么
3. **testers 表** = 核心：所有测试仪器
4. **维护/校准计划表** = 跟踪什么时候该维护/校准
5. **事件日志表** = 记录所有发生的事情
6. **自动化** = EVENT 定期更新，TRIGGER 自动反应

有什么想深入了解的部分吗？比如：

- 如何查询某个用户负责的所有仪器？
- 如何找出所有逾期的维护？
- 如何实现权限检查？

---

## 🔗 **路由映射完整表（从 URL 到代码）**

让我把"用户访问一个网址、系统做什么"串起来：

| 路由                            | 请求方法 | 需要登录？ | 需要角色？               | 对应文件                                                       | 返回什么                               |
| ------------------------------- | -------- | ---------- | ------------------------ | -------------------------------------------------------------- | -------------------------------------- |
| `/`                             | GET      | 否         | 无                       | resources/views/welcome.blade.php                              | 欢迎页                                 |
| `/register`                     | GET/POST | 否         | 无                       | resources/views/livewire/pages/auth/register.blade.php         | 注册表单 + 创建用户                    |
| `/login`                        | GET/POST | 否         | 无                       | resources/views/livewire/pages/auth/login.blade.php            | 登录表单 + 认证                        |
| `/forgot-password`              | GET/POST | 否         | 无                       | resources/views/livewire/pages/auth/forgot-password.blade.php  | 邮箱输入表单 + 发重置链接              |
| `/reset-password/{token}`       | GET/POST | 否         | 无                       | resources/views/livewire/pages/auth/reset-password.blade.php   | 重置密码表单 + 更新密码                |
| `/verify-email`                 | GET      | 是         | 无                       | resources/views/livewire/pages/auth/verify-email.blade.php     | 邮箱验证提示                           |
| `/confirm-password`             | GET/POST | 是         | 无                       | resources/views/livewire/pages/auth/confirm-password.blade.php | 二次密码确认                           |
| `/dashboard`                    | GET      | 是         | 无                       | resources/views/dashboard.blade.php                            | **仪表盘**（包含 EventBox + Calendar） |
| `/profile`                      | GET      | 是         | 无                       | resources/views/profile/edit.blade.php                         | 个人资料编辑页                         |
| `/user-roles`                   | GET      | 是         | Admin                    | resources/views/user-roles.blade.php                           | **角色管理页**（包含 EditUserRoles）   |
| `/api/v1/auth/register`         | POST     | 否         | 无                       | app/Http/Controllers/Api/AuthController.php                    | JSON: token + user 信息                |
| `/api/v1/auth/login`            | POST     | 否         | 无                       | app/Http/Controllers/Api/AuthController.php                    | JSON: token + user 信息                |
| `/api/v1/auth/logout`           | POST     | 是         | 无                       | app/Http/Controllers/Api/AuthController.php                    | JSON: success 消息                     |
| `/api/v1/customers`             | GET/POST | 是         | manager/admin            | app/Http/Controllers/Api/TesterCustomerController.php          | JSON: 客户列表/创建                    |
| `/api/v1/testers`               | GET/POST | 是         | manager/admin            | app/Http/Controllers/Api/TesterController.php                  | JSON: 仪器列表/创建                    |
| `/api/v1/fixtures`              | GET/POST | 是         | manager/admin            | app/Http/Controllers/Api/FixtureController.php                 | JSON: 夹具列表/创建                    |
| `/api/v1/maintenance-schedules` | GET/POST | 是         | technician/manager/admin | app/Http/Controllers/Api/MaintenanceScheduleController.php     | JSON: 维护计划列表                     |
| `/api/v1/calibration-schedules` | GET/POST | 是         | technician/manager/admin | app/Http/Controllers/Api/CalibrationScheduleController.php     | JSON: 校准计划列表                     |
| `/api/v1/event-logs`            | GET/POST | 是         | 同上                     | app/Http/Controllers/Api/EventLogController.php                | JSON: 事件日志                         |
| `/api/v1/spare-parts`           | GET/POST | 是         | manager/admin            | app/Http/Controllers/Api/SparePartController.php               | JSON: 备用件列表                       |

---

## 🔄 **Livewire 和前端 JS 的协作（详细例子）**

### **例子 1：仪表盘日历的完整流程**

这是最能展示 Livewire 和 JS 协作的例子：

**第 1 步：后端准备数据（Calendar.php）**

```php
// app/Livewire/Pages/Dashboard/Calendar.php
public function mount()
{
    $this->events = [
        [
            'id' => '1',
            'title' => 'Tester calibration',
            'type' => 'calibration',
            'start' => '2026-03-25T10:00:00',
            'end' => '2026-03-25T11:00:00',
        ],
        // ... 更多事件 ...
    ];

    // 关键：发送信号给前端
    $this->dispatch('calendar-ready');
}
```

**第 2 步：页面把数据绑定到 HTML（Blade 模板）**

```blade
{{-- resources/views/livewire/pages/dashboard/calendar.blade.php --}}
<div class="calendar-container">
    <div
        id="calendar"
        data-events="{{ json_encode($this->events) }}"
    >
    </div>
</div>
```

**第 3 步：前端 JS 监听信号并绘制日历（calendar.js）**

```javascript
// resources/js/calendar.js
document.addEventListener("calendar-ready", function () {
    // 读取后端数据
    const calendarEl = document.getElementById("calendar");
    const events = JSON.parse(calendarEl.dataset.events);

    // 创建日历实例
    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: "dayGridMonth",
        events: events,
    });

    // 渲染到页面
    calendar.render();
});
```

**整个过程是这样工作的：**

```
用户打开 /dashboard
    ↓
Livewire 组件 mount()：准备 $this->events = [...]
    ↓
Blade 页面渲染：把 @json($this->events) 放进 HTML
    ↓
HTML 加载：<div data-events="[...]"> 成为 DOM 节点
    ↓
app.js 加载：导入并执行 calendar.js
    ↓
calendar.js 触发：等待 calendar-ready 事件
    ↓
Livewire 发送：dispatch('calendar-ready')
    ↓
JS 接收：监听器执行，读取 dataset.events
    ↓
FullCalendar：初始化并渲染日历到页面
    ↓
用户看到：漂亮的日历显示
```

### **例子 2：角色管理的用户交互（Livewire 自身）**

这个例子展示 Livewire 的"实时双向绑定"：

**前端页面（edit-user-roles.blade.php）**

```blade
<select wire:model="selectedUserId" wire:change="selectUser">
    <option value="">-- 选择用户 --</option>
    @foreach($users as $user)
        <option value="{{ $user->id }}">{{ $user->name }}</option>
    @endforeach
</select>
```

**后端组件（EditUserRoles.php）**

```php
public class EditUserRoles extends Component
{
    public $selectedUserId = null;
    public $selectedUser = null;

    public function selectUser()
    {
        $this->selectedUser = User::with('roles')->find($this->selectedUserId);
    }
}
```

**用户交互流程：**

```
用户在下拉框里选择"张三"
    ↓ wire:change="selectUser" 触发
Livewire 自动更新 selectedUserId = 3
    ↓
selectUser() 方法执行
    ↓
$selectedUser = User::find(3)  // 查询数据库
    ↓
页面自动刷新显示张三的角色
    ↓ 用户点击"更新"按钮
updateUserRole() 执行
    ↓
$user->syncRoles(['Admin'])  // 改为 Admin 角色
    ↓
页面自动显示：角色已更新
```

**关键点：没有写 AJAX，没有写 fetch，Livewire 自动处理！**

---

## 🌐 **API 和业务模型的对应关系**

### **API 的核心设计**

项目的 API 采用了"资源化"设计。每个主要业务对象都有对应的 API 端点：

| 业务模型                        | 对应 API                        | 对应 Controller               | 对应 Policy               | 对应 FormRequest                        |
| ------------------------------- | ------------------------------- | ----------------------------- | ------------------------- | --------------------------------------- |
| Tester（仪器）                  | `/api/v1/testers`               | TesterController              | TesterPolicy              | StoreTesterRequest, UpdateTesterRequest |
| TesterCustomer（客户）          | `/api/v1/customers`             | TesterCustomerController      | TesterCustomerPolicy      | StoreTesterCustomerRequest              |
| Fixture（夹具）                 | `/api/v1/fixtures`              | FixtureController             | FixturePolicy             | StoreFixtureRequest                     |
| MaintenanceSchedule（维护计划） | `/api/v1/maintenance-schedules` | MaintenanceScheduleController | MaintenanceSchedulePolicy | StoreMaintenanceScheduleRequest         |
| CalibrationSchedule（校准计划） | `/api/v1/calibration-schedules` | CalibrationScheduleController | CalibrationSchedulePolicy | StoreCalibrationScheduleRequest         |
| EventLog（事件日志）            | `/api/v1/event-logs`            | EventLogController            | EventLogPolicy            | StoreEventLogRequest                    |
| SparePart（备用件）             | `/api/v1/spare-parts`           | SparePartController           | SparePartPolicy           | StoreSparePartRequest                   |

### **API 数据流示例**

当前端通过 API 请求时，一个典型的流程是：

```
HTTP 请求：POST /api/v1/testers
请求体：{ name: "Tester 01", customer_id: 5, ... }
    ↓
路由匹配：routes/api.php → TesterController@store
    ↓
验证输入：通过 StoreTesterRequest 自动验证
    - 名字不能为空
    - customer_id 必须存在
    - serial_number 必须唯一
    ↓
权限检查：TesterPolicy::create($user)
    - 用户必须是 manager 或 admin
    ↓
创建数据：Tester::create($validated)
    ↓
返回响应：
{
  "success": true,
  "message": "Tester created successfully",
  "data": { tester 对象 },
  "code": 201
}
```

---

## 🌱 **Seeder：初始化数据的脚本**

### **DatabaseSeeder.php 做了什么**

```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    // 创建测试用户（如果不存在）
    User::firstOrCreate(
        ['email' => 'test@example.com'],
        [
            'name' => 'Test User',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),  // 自动验证
        ]
    );

    // 调用角色创建脚本
    $this->call(RoleSeeder::class);
}
```

### **RoleSeeder.php 做了什么**

```php
// database/seeders/RoleSeeder.php
public function run(): void
{
    // 定义要创建的 3 个角色
    $roles = [
        'Admin',
        'Maintenance Technician',
        'Calibration Specialist'
    ];

    // 依次创建角色
    foreach ($roles as $role) {
        Role::firstOrCreate(['name' => $role]);
    }

    // 创建默认管理员账号
    $user = User::firstOrCreate(
        ['email' => 'admin@example.com'],
        [
            'name' => 'Admin User',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]
    );

    // 把管理员账号分配给 Admin 角色
    $user->assignRole('Admin');
}
```

### **如何运行 Seeder**

```bash
# 运行所有 Seeder
php artisan migrate:fresh --seed

# 只运行 Seeder，不重置数据库
php artisan db:seed

# 运行特定 Seeder
php artisan db:seed --class=RoleSeeder
```

### **为什么需要 Seeder？**

- ✅ 新建项目时快速初始化测试数据
- ✅ 所有开发者运行同样的 Seeder，保证数据一致
- ✅ 不需要手动在数据库里创建初始用户和角色

---

## 🔐 **权限系统的完整讲解**

### **三层权限检查**

项目用了三层来保证安全性：

**第 1 层：路由中间件（routes/web.php）**

```php
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::view('user-roles', 'user-roles')->name('user-roles');
});
```

说明：访问 `/user-roles` 前，先检查：

1. 用户必须登录（`auth`）
2. 用户必须是 Admin 角色（`role:Admin`）

**第 2 层：API 权限检查（在 Controller 里）**

```php
// app/Http/Controllers/Api/TesterController.php
public function store(StoreTesterRequest $request)
{
    // 先检查权限：用户能不能创建 Tester？
    $this->authorize('create', Tester::class);

    // 通过了才执行创建
    return Tester::create($request->validated());
}
```

**第 3 层：Policy 规则（BasePolicy.php）**

```php
// app/Policies/TesterPolicy.php
public function create(User $user): bool
{
    // 只有 manager 或 admin 能创建
    return $this->hasAnyRole($user, ['admin', 'manager']);
}
```

### **角色别名的作用**

有时候代码里用"规范名"（如 `admin`），但数据库里存的是"业务名"（如 `Admin`）。BasePolicy 的别名映射解决了这个问题：

```php
protected const ROLE_ALIASES = [
    'admin' => ['admin', 'Admin'],                        // 兼容两种写法
    'manager' => ['manager', 'Maintenance Technician'],   // 同上
    'technician' => ['technician', 'Calibration Specialist'],
    'guest' => ['guest', 'Guest'],
];

// 所以代码里写 ['admin', 'manager'] 时，
// 会自动检查数据库中的 'Admin' 和 'Maintenance Technician'
```

---

## ⚠️ **模拟数据位置大全**

目前项目有**很多地方还在用模拟数据**，而不是真实数据库查询。初学者一定要搞清楚哪些是"演示"数据：

### **✅ 已实现（使用真实数据库）**

| 功能     | 原因                                 | 对应代码                      |
| -------- | ------------------------------------ | ----------------------------- |
| 用户注册 | 真实存入 users 表                    | register.blade.php            |
| 用户登录 | 真实查询 users 表                    | LoginForm.php                 |
| 邮箱验证 | 真实检查 email_verified_at           | Auth::check + verified 中间件 |
| 密码重置 | 真实存入 password_reset_tokens 表    | reset-password.blade.php      |
| 角色分配 | 真实存入 model_has_roles 表          | EditUserRoles.php             |
| 权限检查 | 真实查询 roles 和 model_has_roles 表 | BasePolicy.php                |

### **❌ 还在用模拟数据（需要后续开发）**

| 功能           | 当前情况                  | 位置                              | 计划中                                                |
| -------------- | ------------------------- | --------------------------------- | ----------------------------------------------------- |
| 仪表盘事件列表 | 硬编码假数据              | EventBox.php（mount 方法）        | 应该查 tester_event_logs 表                           |
| 仪表盘日历事件 | 硬编码假数据              | Calendar.php（mount 方法）        | 应该查 maintenance_schedules + calibration_schedules  |
| 问题列表       | 不存在                    | 无                                | 应该查 tester_event_logs 中 event_type='issue' 的记录 |
| 仪器管理       | API 虽然写了，但无前端 UI | TesterController + 数据库有 Model | 需要前端列表和编辑页                                  |
| 维护计划       | API 虽然写了，但无前端 UI | MaintenanceScheduleController     | 需要前端界面                                          |

### **具体看哪些代码可以找到模拟数据**

**EventBox.php - 第 1 个模拟数据来源：**

```php
public function mount($title = "All Events", $type = 'all', $limit = 4)
{
    // TODO: get db data  ← 明确说明这是模拟数据！
    $mockItems = [
        [
            'type' => 'issue',
            'tester' => 'Tester 01',
            'date' => now()->addDays(2),
        ],
        // ... 更多假数据 ...
    ];

    // 以下是数据处理，最后赋值给 $this->items
    $this->items = $mockItems;
}
```

**Calendar.php - 第 2 个模拟数据来源：**

```php
public function mount()
{
    $this->events = [  // 全部硬编码
        [
            'id' => '1',
            'title' => 'Tester calibration',
            'type' => 'calibration',
            'start' => '2026-03-25T10:00:00',
            'end' => '2026-03-25T11:00:00',
        ],
        // ... 更多假日历事件 ...
    ];

    $this->dispatch('calendar-ready');
}
```

---

## 📊 **项目的"真实程度"评分**

如果把项目分成 10 个部分，现在完成度如何：

| 部分                  | 完成度  | 说明                                     |
| --------------------- | ------- | ---------------------------------------- |
| **1. 用户认证系统**   | ✅ 100% | 注册、登录、邮箱验证、密码重置都真实可用 |
| **2. 权限管理**       | ✅ 95%  | 框架搭好，只缺一些细粒度权限定义         |
| **3. 角色分配界面**   | ✅ 100% | Admin 能给用户分配角色，数据真实存储     |
| **4. 仪表盘界面**     | 🟡 20%  | 页面漂亮，但数据是假的                   |
| **5. 仪器管理 API**   | 🟡 60%  | API 代码写完了，但没有前端界面           |
| **6. 维护计划 API**   | 🟡 60%  | 同上                                     |
| **7. 校准计划 API**   | 🟡 60%  | 同上                                     |
| **8. 事件日志记录**   | 🟡 50%  | API 写了，前端用不了，数据库表已定义     |
| **9. 自动计算和提醒** | ❌ 0%   | MySQL EVENT 和 TRIGGER 还没激活          |
| **10. API 完整测试**  | ✅ 100% | 有 120 个通过测试                        |

---

## 🎯 **初学者必须理解的 5 个关键点**

1. **模拟数据 vs 真实数据**：
    - EventBox 和 Calendar 用的是 `$mockItems`，不是数据库查询
    - 用户管理和权限用的是真实 users 表和 roles 表

2. **Livewire 的魔力**：
    - 你写 PHP，Livewire 自动生成前后端通信代码
    - 不用手写 AJAX，不用手写 fetch

3. **API 和网页是分开的**：
    - `/api/v1/*` 是给程序调用的接口（JSON 数据）
    - `/dashboard` 是给人看的网页（HTML）

4. **权限有三层保护**：
    - 路由中间件：拦在最前面
    - Controller 的 authorize：拦在逻辑前面
    - Policy：定义具体规则

5. **Seeder 的作用**：
    - 避免重复手动输入
    - 保证团队所有人的初始数据一样
    - 方便测试时恢复原始数据
