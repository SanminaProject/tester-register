下面我按“整体结构 → 页面流程 → 数据怎么流动”的顺序，把这个项目讲成一份适合初学者阅读的学习笔记。你可以把它理解成一套“仪器/测试设备管理系统”：它有登录注册、仪表盘、用户角色管理，还有一套已经搭好的 API。

## 1. 先用一句话理解这个项目

这个项目是一个 Laravel 网站，核心目标有四个：

- 让用户登录、注册、找回密码、验证邮箱
- 登录后进入仪表盘，看事件和日历
- 管理员可以给用户分配角色
- 后端还提供一套 `/api/v1` 的接口，给其他程序或前端客户端使用

你可以先把它分成三层：

- 页面层：浏览器看到的 Blade/Livewire 页面
- 交互层：Livewire Volt、Livewire Component、前端 JS
- 数据层：数据库、模型、Seeder、权限系统、API

## 2. 这个项目的主要目录

先记住这几个入口，理解项目会快很多：

- 路由入口：[routes/web.php](routes/web.php)、[routes/auth.php](routes/auth.php)、[routes/userRoles.php](routes/userRoles.php)、[routes/api.php](routes/api.php)
- 页面视图：[resources/views/dashboard.blade.php](resources/views/dashboard.blade.php)、[resources/views/user-roles.blade.php](resources/views/user-roles.blade.php)
- Livewire 组件：[app/Livewire/Pages/Dashboard/EventBox.php](app/Livewire/Pages/Dashboard/EventBox.php)、[app/Livewire/Pages/Dashboard/Calendar.php](app/Livewire/Pages/Dashboard/Calendar.php)、[app/Livewire/Pages/Admin/EditUserRoles.php](app/Livewire/Pages/Admin/EditUserRoles.php)
- 登录表单逻辑：[app/Livewire/Forms/LoginForm.php](app/Livewire/Forms/LoginForm.php)
- 用户和权限：[app/Models/User.php](app/Models/User.php)、[app/Policies/BasePolicy.php](app/Policies/BasePolicy.php)
- 初始化数据：[database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php)、[database/seeders/RoleSeeder.php](database/seeders/RoleSeeder.php)
- 前端资源：[resources/js/app.js](resources/js/app.js)、[resources/js/calendar.js](resources/js/calendar.js)、[resources/css/app.css](resources/css/app.css)

如果你只想先记最关键的文件，可以先盯住：路由、`Livewire` 组件、`User` 模型、`Seeder`、`app.js`。

## 3. 路由到底负责什么

路由就是“用户访问某个网址后，系统要把他送到哪里”的规则。

### 3.1 网页路由

[routes/web.php](routes/web.php) 里定义了网站的普通页面：

- `/` → 欢迎页 `welcome`
- `/dashboard` → 仪表盘 `dashboard`，需要登录并且通过邮箱验证
- `/profile` → 个人资料页 `profile`，需要登录

这个文件最后还引入了另外两个路由文件：

- [routes/auth.php](routes/auth.php)
- [routes/userRoles.php](routes/userRoles.php)

这表示认证页面和管理员页面被拆开管理，结构更清楚。

### 3.2 认证路由

[routes/auth.php](routes/auth.php) 里是登录注册相关页面，使用的是 Livewire Volt。

你会看到这种写法：

```php
Volt::route('login', 'pages.auth.login')
```

这句话的意思不是“跳到一个传统控制器”，而是“这个路由直接绑定一个 Livewire Volt 页面”。

认证页面包括：

- 注册
- 登录
- 忘记密码
- 重置密码
- 验证邮箱
- 确认密码

### 3.3 管理员路由

[routes/userRoles.php](routes/userRoles.php) 里只有一个主要页面：

- `/user-roles`

它要求同时满足两个条件：

- `auth`：必须登录
- `role:Admin`：必须是 Admin 角色

所以它是典型的“后台管理页面”，普通用户进不去。

### 3.4 API 路由

[routes/api.php](routes/api.php) 里是接口层，统一放在 `/api/v1` 前缀下。

它分两类：

- 公开接口：`/auth/login`、`/auth/register`
- 受保护接口：其余资源接口都需要 `auth:sanctum`

这说明项目不仅有网页端，还有一套给程序调用的 JSON 接口。

## 4. 用户看到的网页是怎么来的

浏览器访问一个地址时，通常会经历这几步：

1. 请求先进入路由文件
2. 路由判断要不要登录、要不要管理员权限
3. 返回一个 Blade 页面
4. 页面里再嵌入 Livewire 组件
5. 如果页面需要交互，Livewire 和前端 JS 一起工作

你可以把 Blade 理解成“页面骨架”，把 Livewire 理解成“会自己处理数据和交互的页面部件”。

## 5. 登录、注册、找回密码的完整流程

这一部分是新手最值得先理解的，因为它是大多数 Web 项目的共同基础。

### 5.1 注册流程

注册页在 [resources/views/livewire/pages/auth/register.blade.php](resources/views/livewire/pages/auth/register.blade.php)。

它的核心逻辑是：

1. 用户输入 `name / email / password / password_confirmation`
2. 点击提交，触发 `register()` 方法
3. 程序先做验证
4. 密码会被 `Hash::make()` 加密
5. `User::create()` 创建新用户
6. 触发 `Registered` 事件
7. 自动登录新用户
8. 跳转到 Dashboard

你可以把它记成一句话：

“注册 = 校验 → 创建用户 → 自动登录 → 进入仪表盘。”

### 5.2 登录流程

登录页在 [resources/views/livewire/pages/auth/login.blade.php](resources/views/livewire/pages/auth/login.blade.php)。

真正的认证细节放在 [app/Livewire/Forms/LoginForm.php](app/Livewire/Forms/LoginForm.php)。

它的流程可以拆成三层理解：

1. 页面收集邮箱、密码、记住我
2. `login()` 方法先做表单验证
3. `LoginForm::authenticate()` 调用 `Auth::attempt()` 去核对账号密码

如果密码不对，就抛出验证错误；如果成功，就清除登录失败记录、刷新 session，然后跳转到 Dashboard。

另外，这个表单还带了一个简单的限流机制：如果同一个邮箱和 IP 短时间内尝试太多次，会暂时锁定，避免暴力猜密码。

你可以把登录理解成：

“前端填表 → 后端验密码 → 成功后建立会话 → 进入系统。”

### 5.3 忘记密码

忘记密码页在 [resources/views/livewire/pages/auth/forgot-password.blade.php](resources/views/livewire/pages/auth/forgot-password.blade.php)。

它的逻辑很直接：

1. 用户输入邮箱
2. 程序调用 `Password::sendResetLink()`
3. 系统把重置密码链接发到邮箱

这一步不会直接改密码，只是先发一个安全的重置入口。

### 5.4 重置密码

重置密码页在 [resources/views/livewire/pages/auth/reset-password.blade.php](resources/views/livewire/pages/auth/reset-password.blade.php)。

它的流程是：

1. 用户从邮件里的链接进入页面
2. 页面带着 `token` 和 `email`
3. 用户输入新密码和确认密码
4. 程序调用 `Password::reset()`
5. 密码更新成功后跳回登录页

你可以把它理解为：

“邮箱里的 token 是一次性的钥匙，只有拿着它才能安全重设密码。”

### 5.5 验证邮箱和确认密码

验证邮箱页在 [resources/views/livewire/pages/auth/verify-email.blade.php](resources/views/livewire/pages/auth/verify-email.blade.php)。

它让用户在注册后重新发送验证邮件，或者退出登录。

确认密码页在 [resources/views/livewire/pages/auth/confirm-password.blade.php](resources/views/livewire/pages/auth/confirm-password.blade.php)。

它的作用是：当你要做敏感操作时，系统会再要求你输入一次密码，确认你真的是本人。

## 6. 仪表盘为什么能显示两块卡片和一个日历

仪表盘页面在 [resources/views/dashboard.blade.php](resources/views/dashboard.blade.php)。

它做的事情并不复杂：

1. 先套一层页面布局
2. 显示两个事件卡片组件
3. 再显示一个日历组件

这两个组件分别是：

- [app/Livewire/Pages/Dashboard/EventBox.php](app/Livewire/Pages/Dashboard/EventBox.php)
- [app/Livewire/Pages/Dashboard/Calendar.php](app/Livewire/Pages/Dashboard/Calendar.php)

### 6.1 EventBox 是什么

EventBox 可以理解成“事件摘要卡片”。

它接收三个参数：

- `title`
- `type`
- `limit`

然后在 `mount()` 里准备一组模拟数据 `mockItems`。

现在它还没有连数据库，代码里也明确写了 `TODO: get db data`。

它的处理过程是：

1. 先准备一堆假的事件数据
2. 根据 `type` 过滤内容
3. 按日期排序
4. 只保留前 `limit` 条

所以它本质上是“先拿假数据把页面和交互搭起来”，以后可以再换成真数据库。

### 6.2 Calendar 是什么

Calendar 组件在 [app/Livewire/Pages/Dashboard/Calendar.php](app/Livewire/Pages/Dashboard/Calendar.php)。

它也还是模拟数据，不是数据库查询。

它的思路是：

1. 在后端准备一个事件数组
2. 调用 `$this->dispatch('calendar-ready')`
3. 前端 JS 监听这个事件
4. JS 用 FullCalendar 把事件画出来

所以这个日历不是“Livewire 自己画出来的”，而是“Livewire 提供数据，前端 JS 负责渲染”。

### 6.3 前端日历是怎么工作的

日历相关的前端入口在：

- [resources/js/app.js](resources/js/app.js)
- [resources/js/calendar.js](resources/js/calendar.js)
- [resources/css/app.css](resources/css/app.css)

`app.js` 只是把 `bootstrap` 和 `calendar` 导入进来。真正初始化日历的是 `calendar.js`。

`calendar.js` 做的事情可以概括成：

1. 监听 `calendar-ready`
2. 读取 `#calendar` 元素上的 `dataset.events`
3. 把 JSON 解析成事件数组
4. 用 FullCalendar 初始化月视图、周视图、日视图

这就是 Livewire 和前端 JS 协作的典型例子：

- Livewire 负责“后端状态和事件触发”
- JS 负责“复杂的前端可视化组件”

## 7. 用户角色管理是怎么做的

管理员角色页面在 [resources/views/user-roles.blade.php](resources/views/user-roles.blade.php)，核心组件在 [app/Livewire/Pages/Admin/EditUserRoles.php](app/Livewire/Pages/Admin/EditUserRoles.php) 和 [resources/views/livewire/pages/admin/edit-user-roles.blade.php](resources/views/livewire/pages/admin/edit-user-roles.blade.php)。

这个功能的流程很适合初学者练习，因为它同时包含“查询数据、显示下拉框、修改关系、刷新页面状态”这几个常见步骤。

### 7.1 它先做了什么

`mount()` 会一次性读取：

- 所有用户，并带上他们的角色
- 所有角色列表

这意味着页面一打开，管理员就能看到可选用户和可选角色。

### 7.2 选择用户后会发生什么

当管理员从下拉框里选中一个用户，`selectUser()` 会把这个用户加载出来，并顺手把当前角色显示到页面上。

### 7.3 更新角色和删除角色

`updateUserRole()` 会把用户的角色改成选中的那个角色；`removeUserRole()` 会把角色移除。

这里最值得你记住的是 `syncRoles()` 和 `removeRole()`：

- `syncRoles()`：把用户的角色替换成新的那一个
- `removeRole()`：把某个角色拿掉

页面本身并不复杂，核心是理解“用户”和“角色”在数据库里是关联关系，不是单纯写死在页面上的文字。

## 8. 用户模型和权限系统为什么这么重要

用户模型在 [app/Models/User.php](app/Models/User.php)。

它启用了几个非常关键的能力：

- `HasApiTokens`：支持 Sanctum API token
- `HasRoles`：支持 Spatie Permission 的角色系统

这意味着同一个用户既可以登录网页，也可以拿 API token 调接口，还可以拥有角色权限。

### 8.1 角色是怎么判定的

[app/Policies/BasePolicy.php](app/Policies/BasePolicy.php) 做了一件很重要的事：角色别名映射。

它把两套命名方式对起来了：

- API 里常用的小写名字：`admin`、`manager`、`technician`、`guest`
- 数据库里已经存在的角色名：`Admin`、`Maintenance Technician`、`Calibration Specialist`、`Guest`

这样做的好处是：

- 代码里可以写统一的“规范角色名”
- 真实数据库里的角色名可以保留更适合业务阅读的名字

### 8.2 策略文件是干什么的

`app/Policies/` 下面的一组 Policy 文件，负责决定“谁能看、谁能新增、谁能修改、谁能删除”。

例如：

- [app/Policies/TesterPolicy.php](app/Policies/TesterPolicy.php)
- [app/Policies/TesterCustomerPolicy.php](app/Policies/TesterCustomerPolicy.php)

它们的逻辑都很像：

- `view()`：更多角色可看
- `create()`：更少角色可创建
- `update()`：通常管理员和经理可改
- `delete()`：通常只有管理员可删

你可以把 Policy 理解成“权限门卫”。控制器在执行真正操作前，会先问它一句：这个用户能不能做？

## 9. API 层在做什么

这个项目不只是网页，它还有一套完整的 REST API。

### 9.1 API 的入口

API 路由在 [routes/api.php](routes/api.php)，统一以 `/api/v1` 开头。

主要资源包括：

- customers
- testers
- fixtures
- maintenance-schedules
- calibration-schedules
- event-logs
- spare-parts

还有认证接口：

- `POST /api/v1/auth/login`
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/logout`

### 9.2 API 怎么做登录保护

API 用的是 `auth:sanctum`。

你可以这样理解：

- 网页端更多依赖 session
- API 端更多依赖 token

### 9.3 API 为什么适合做成这样

因为很多程序不一定通过浏览器访问这个系统，可能会有：

- 手机端
- 内部管理工具
- 其他系统调用

所以 API 的存在让这个项目不只是“一个网页”，而是一个可以被别的程序复用的后端。

### 9.4 API 里有哪些核心类

控制器在 [app/Http/Controllers/Api/](app/Http/Controllers/Api/) 下。

请求验证类在 [app/Http/Requests/Api/](app/Http/Requests/Api/) 下。

这表示项目已经把“业务逻辑”和“输入校验”拆开了：

- Controller 负责接收请求、调用模型、返回响应
- FormRequest 负责检查输入是否合法

这是一种很标准、也很适合长期维护的写法。

### 9.5 这个项目里的主要业务模型

API 主要围绕这些模型工作：

- TesterCustomer
- Tester
- Fixture
- MaintenanceSchedule
- CalibrationSchedule
- EventLog
- SparePart

如果你把它们串起来看，就会发现这是一个“测试设备和维护流程管理系统”：

- Customer 是客户
- Tester 是设备/测试对象
- Fixture 是治具或夹具
- MaintenanceSchedule 是维护计划
- CalibrationSchedule 是校准计划
- EventLog 是事件记录
- SparePart 是备件

## 10. 初始数据是怎么来的

数据库初始化从 [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php) 开始。

它会先创建一个测试用户：

- 邮箱：`test@example.com`
- 密码：`password123`

然后再调用 [database/seeders/RoleSeeder.php](database/seeders/RoleSeeder.php)。

`RoleSeeder` 会创建角色并生成一个管理员账号：

- 角色：`Admin`、`Maintenance Technician`、`Calibration Specialist`
- 默认管理员邮箱：`admin@example.com`
- 默认管理员密码：`12345678`

这部分的价值在于：你不用自己手工建第一批账号，项目可以直接跑起来。

## 11. 前端资源是怎么打包的

Laravel 这里用的是 Vite。

入口文件很简单：

- [resources/js/app.js](resources/js/app.js)
- [resources/css/app.css](resources/css/app.css)

你可以把它理解成：

- `app.js`：把需要的 JavaScript 都集中导入
- `app.css`：把样式入口集中导入
- Vite：负责把这些资源编译、热更新、打包

特别要记住的是：

- `app.js` 导入了 `calendar.js`
- `app.css` 导入了 `calendar.css`

所以日历能显示，不只是因为后端有数据，还因为前端资源也被正确加载了。

## 12. 把整个项目按“用户操作”串起来

如果你想从“用户视角”理解整个系统，可以按下面的顺序看：

### 场景 A：普通用户第一次进来

1. 访问 `/`
2. 看到欢迎页
3. 去注册或登录
4. 注册后系统自动登录
5. 跳转到 `/dashboard`
6. 看到事件卡片和日历

### 场景 B：用户登录失败

1. 输入错误邮箱或密码
2. `Auth::attempt()` 失败
3. 表单显示错误信息
4. 如果连续尝试太多次，会触发限流

### 场景 C：管理员改角色

1. 管理员先登录
2. 访问 `/user-roles`
3. 选择一个用户
4. 选择一个角色
5. 点击更新或删除
6. 页面刷新用户角色状态

### 场景 D：程序通过 API 调数据

1. 客户端拿着 Sanctum token 请求 `/api/v1/*`
2. 后端先做认证
3. 再走 Policy 权限判断
4. 通过 FormRequest 验证输入
5. 返回统一格式的 JSON

## 13. 现在这个项目哪些地方是真实功能，哪些地方还是演示数据

目前你最需要分清这一点：

- 真实功能：登录、注册、找回密码、邮箱验证、角色管理、API、权限判断、Seeder、路由、前端打包
- 演示数据：仪表盘的 EventBox 和 Calendar 目前还是模拟数据，还没有接真正数据库

这很常见，因为开发者通常会先把界面和交互跑起来，再把假数据替换成真实业务数据。

## 14. 如果你要继续深入，最适合先看的文件

我建议你下一步优先看这几个文件：

1. [routes/auth.php](routes/auth.php)
2. [resources/views/livewire/pages/auth/login.blade.php](resources/views/livewire/pages/auth/login.blade.php)
3. [app/Livewire/Forms/LoginForm.php](app/Livewire/Forms/LoginForm.php)
4. [app/Livewire/Pages/Dashboard/Calendar.php](app/Livewire/Pages/Dashboard/Calendar.php)
5. [resources/js/calendar.js](resources/js/calendar.js)
6. [app/Livewire/Pages/Admin/EditUserRoles.php](app/Livewire/Pages/Admin/EditUserRoles.php)
7. [app/Policies/BasePolicy.php](app/Policies/BasePolicy.php)
8. [routes/api.php](routes/api.php)

## 15. 逐文件导读版

这一部分不是重新讲一遍项目，而是把前面的总览拆成“你打开某个文件时应该先看什么”的版本。你可以把它当成读代码的导航图。

### 15.1 路由文件

#### [routes/web.php](routes/web.php)

这个文件负责普通网页入口。你先看它，就能知道用户访问 `/`、`/dashboard`、`/profile` 时会去哪里。

初学者重点：

- 这里是“整个网站的门口”
- `middleware(['auth', 'verified'])` 表示页面有访问条件
- 最后 `require` 了认证和管理员路由文件，说明它是总入口

#### [routes/auth.php](routes/auth.php)

这个文件负责登录、注册、找回密码、重置密码、邮箱验证、确认密码。

初学者重点：

- 这里使用 Livewire Volt，而不是传统控制器
- `guest` 路由表示“没登录的人才能看”
- `auth` 路由表示“登录后才能看”

#### [routes/userRoles.php](routes/userRoles.php)

这个文件只负责管理员角色管理页面。

初学者重点：

- 这里是一个典型的“权限门禁”文件
- `role:Admin` 说明只有管理员能进入
- 它把管理页面和普通页面分开，结构更清楚

#### [routes/api.php](routes/api.php)

这个文件负责 API 接口。

初学者重点：

- `/api/v1` 是接口统一前缀
- `auth:sanctum` 表示接口要用 token 认证
- 这里的资源路由很多，说明系统除了网页端，也支持程序调用

### 15.2 登录注册页面

#### [resources/views/livewire/pages/auth/register.blade.php](resources/views/livewire/pages/auth/register.blade.php)

这个文件负责注册页的界面和注册动作。

初学者重点：

- 顶部 PHP 区域里有注册逻辑
- 页面底部是表单界面
- 注册成功后会自动登录并跳转 Dashboard

#### [resources/views/livewire/pages/auth/login.blade.php](resources/views/livewire/pages/auth/login.blade.php)

这个文件负责登录页的界面。

初学者重点：

- 它只负责“页面 + 提交入口”
- 真正的认证逻辑在 [app/Livewire/Forms/LoginForm.php](app/Livewire/Forms/LoginForm.php)
- 你会在这里看到记住我、忘记密码、错误提示等常见登录功能

#### [resources/views/livewire/pages/auth/forgot-password.blade.php](resources/views/livewire/pages/auth/forgot-password.blade.php)

这个文件负责输入邮箱后发送重置链接。

初学者重点：

- 逻辑非常短，适合理解“表单提交 → 发送邮件”的基础流程
- 这里不会直接改密码，只负责发重置入口

#### [resources/views/livewire/pages/auth/reset-password.blade.php](resources/views/livewire/pages/auth/reset-password.blade.php)

这个文件负责真正的重置密码。

初学者重点：

- 你会看到 `token` 和 `email`
- 这说明它必须通过邮件链接进入
- 密码成功更新后会返回登录页

#### [resources/views/livewire/pages/auth/verify-email.blade.php](resources/views/livewire/pages/auth/verify-email.blade.php)

这个文件负责邮箱验证提示和重新发送验证邮件。

初学者重点：

- 它是注册后的后续步骤
- 主要作用是告诉用户“请先验证邮箱再继续”

#### [resources/views/livewire/pages/auth/confirm-password.blade.php](resources/views/livewire/pages/auth/confirm-password.blade.php)

这个文件负责敏感操作前的二次密码确认。

初学者重点：

- 这是一个安全检查页
- 它的存在说明项目对危险操作做了额外保护

### 15.3 仪表盘页面

#### [resources/views/dashboard.blade.php](resources/views/dashboard.blade.php)

这个文件是仪表盘页面的外壳。

初学者重点：

- 它本身不负责复杂逻辑
- 它只是把两个 EventBox 和一个 Calendar 放进页面里
- 真正的数据逻辑在 Livewire 组件里

#### [app/Livewire/Pages/Dashboard/EventBox.php](app/Livewire/Pages/Dashboard/EventBox.php)

这个文件负责事件摘要卡片。

初学者重点：

- 目前用的是模拟数据
- 它会按类型筛选、按日期排序、再截取数量
- 你可以把它当成“数据准备器”

#### [app/Livewire/Pages/Dashboard/Calendar.php](app/Livewire/Pages/Dashboard/Calendar.php)

这个文件负责日历数据和触发前端渲染事件。

初学者重点：

- 它也在用模拟数据
- 它通过 `calendar-ready` 把信号发给前端
- 它的作用是“把后端数据交给前端日历插件”

#### [resources/js/calendar.js](resources/js/calendar.js)

这个文件负责把日历真正画出来。

初学者重点：

- 它监听 `calendar-ready`
- 它从 DOM 里读取事件数据
- 它用 FullCalendar 初始化月/周/日视图

### 15.4 角色管理页面

#### [resources/views/user-roles.blade.php](resources/views/user-roles.blade.php)

这个文件是管理员角色管理页的外壳。

初学者重点：

- 它套用了通用布局
- 真正的管理界面嵌入在 Livewire 组件里
- 你可以把它看成“装组件的页面”

#### [app/Livewire/Pages/Admin/EditUserRoles.php](app/Livewire/Pages/Admin/EditUserRoles.php)

这个文件是真正的角色管理逻辑。

初学者重点：

- 它先读取所有用户和角色
- 选择用户后显示当前角色
- 更新或删除角色时会刷新页面状态

#### [resources/views/livewire/pages/admin/edit-user-roles.blade.php](resources/views/livewire/pages/admin/edit-user-roles.blade.php)

这个文件负责角色管理界面的具体表单和按钮。

初学者重点：

- 这里有用户下拉框
- 这里有角色下拉框
- 这里有更新和删除按钮
- 它是“界面层”，真正逻辑在上面的 PHP 类里

### 15.5 登录和表单逻辑

#### [app/Livewire/Forms/LoginForm.php](app/Livewire/Forms/LoginForm.php)

这个文件负责登录校验和限流。

初学者重点：

- 它不是页面，是一个表单对象
- 它负责验证邮箱、密码、记住我
- 它负责调用 `Auth::attempt()`
- 它还负责防止短时间内反复试错

### 15.6 用户、权限和策略

#### [app/Models/User.php](app/Models/User.php)

这个文件是用户模型。

初学者重点：

- 它表示数据库里的用户记录
- 它启用了角色功能
- 它也支持 API token

#### [app/Policies/BasePolicy.php](app/Policies/BasePolicy.php)

这个文件负责角色名兼容。

初学者重点：

- 它把小写规范名和数据库角色名对齐
- 这样项目里不同地方写角色名时不会乱
- 它是所有 Policy 的共同工具类

#### [app/Policies/TesterPolicy.php](app/Policies/TesterPolicy.php)

这个文件决定 Tester 相关操作谁能做。

初学者重点：

- `view` 通常更多角色可看
- `create`、`update`、`delete` 的限制更严格
- 它体现了“权限不是写在页面上，而是写在规则里”

#### [app/Policies/TesterCustomerPolicy.php](app/Policies/TesterCustomerPolicy.php)

这个文件决定 TesterCustomer 相关操作谁能做。

初学者重点：

- 它和 TesterPolicy 的结构类似
- 它展示了不同业务对象可以有不同权限规则

### 15.7 数据初始化

#### [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php)

这个文件负责最基础的测试数据初始化。

初学者重点：

- 它会创建一个普通测试用户
- 它会调用 RoleSeeder
- 它让项目第一次启动时就有可用账号

#### [database/seeders/RoleSeeder.php](database/seeders/RoleSeeder.php)

这个文件负责角色和管理员账号。

初学者重点：

- 它创建角色
- 它创建默认管理员
- 它把管理员绑定到 Admin 角色

### 15.8 前端入口

#### [resources/js/app.js](resources/js/app.js)

这个文件是前端 JavaScript 的总入口。

初学者重点：

- 它把基础脚本和日历脚本统一加载进来
- 你可以把它看成“前端启动文件”

#### [resources/css/app.css](resources/css/app.css)

这个文件是前端样式入口。

初学者重点：

- 它把 Tailwind 和日历样式合并起来
- 它决定页面最终怎么显示

### 15.9 读文件的顺序建议

如果你想真正顺着源码学这个项目，我建议按这个顺序读：

1. [routes/web.php](routes/web.php)
2. [routes/auth.php](routes/auth.php)
3. [resources/views/livewire/pages/auth/login.blade.php](resources/views/livewire/pages/auth/login.blade.php)
4. [app/Livewire/Forms/LoginForm.php](app/Livewire/Forms/LoginForm.php)
5. [resources/views/dashboard.blade.php](resources/views/dashboard.blade.php)
6. [app/Livewire/Pages/Dashboard/EventBox.php](app/Livewire/Pages/Dashboard/EventBox.php)
7. [app/Livewire/Pages/Dashboard/Calendar.php](app/Livewire/Pages/Dashboard/Calendar.php)
8. [resources/js/calendar.js](resources/js/calendar.js)
9. [resources/views/user-roles.blade.php](resources/views/user-roles.blade.php)
10. [app/Livewire/Pages/Admin/EditUserRoles.php](app/Livewire/Pages/Admin/EditUserRoles.php)
11. [app/Models/User.php](app/Models/User.php)
12. [app/Policies/BasePolicy.php](app/Policies/BasePolicy.php)
13. [routes/api.php](routes/api.php)
14. [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php)
15. [database/seeders/RoleSeeder.php](database/seeders/RoleSeeder.php)

## 16. 逐文件逐段注释版

这一部分会更细一些。它不只是告诉你“这个文件做什么”，而是直接按代码块解释“这一段为什么存在、它和下一段怎么接起来”。

### 16.1 [routes/web.php](routes/web.php)

```php
Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
	->middleware(['auth', 'verified'])
	->name('dashboard');

Route::view('profile', 'profile')
	->middleware(['auth'])
	->name('profile');

require __DIR__.'/auth.php';
require __DIR__.'/userRoles.php';
```

这一段是在定义整个网页站点的主入口。

- 第一行把 `/` 指向欢迎页，表示任何人都能先看到首页。
- 第二段把 `/dashboard` 绑定到 `dashboard` 页面，而且加了两个条件：必须登录、必须验证邮箱。
- 第三段把 `/profile` 绑定到个人资料页，只要求登录。
- 最后两行把认证路由和管理员路由拆出去单独管理，这样主路由文件就不会太乱。

你可以把这个文件理解成“网站大门口的导航牌”。

### 16.2 [routes/auth.php](routes/auth.php)

```php
Route::middleware('guest')->group(function () {
	Volt::route('register', 'pages.auth.register')
		->name('register');

	Volt::route('login', 'pages.auth.login')
		->name('login');

	Volt::route('forgot-password', 'pages.auth.forgot-password')
		->name('password.request');

	Volt::route('reset-password/{token}', 'pages.auth.reset-password')
		->name('password.reset');
});

Route::middleware('auth')->group(function () {
	Volt::route('verify-email', 'pages.auth.verify-email')
		->name('verification.notice');

	Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
		->middleware(['signed', 'throttle:6,1'])
		->name('verification.verify');

	Volt::route('confirm-password', 'pages.auth.confirm-password')
		->name('password.confirm');
});
```

这一段把认证相关页面按“未登录”和“已登录”分开了。

- `guest` 组表示只有没登录的人才能进，比如注册、登录、找回密码。
- `auth` 组表示登录后才能进，比如邮箱验证提示、确认密码。
- `reset-password/{token}` 说明重置密码页面不是随便打开的，它必须拿着邮件里的 token 才能进。
- `VerifyEmailController` 是一个专门处理邮箱签名链接的控制器，说明这个流程比普通页面多了一层安全校验。

你可以把这里理解成“登录前后两个世界”的分界线。

### 16.3 [resources/views/livewire/pages/auth/register.blade.php](resources/views/livewire/pages/auth/register.blade.php)

```php
new #[Layout('layouts.guest')] class extends Component
{
	public string $name = '';
	public string $email = '';
	public string $password = '';
	public string $password_confirmation = '';

	public function register(): void
	{
		$validated = $this->validate([
			'name' => ['required', 'string', 'max:255'],
			'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
			'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
		]);

		$validated['password'] = Hash::make($validated['password']);

		event(new Registered($user = User::create($validated)));

		Auth::login($user);

		$this->redirect(route('dashboard', absolute: false), navigate: true);
	}
};
```

这一段是注册页最重要的逻辑。

- `Layout('layouts.guest')` 表示它用的是游客布局，也就是还没登录时看到的页面样式。
- `$name`、`$email`、`$password`、`$password_confirmation` 是表单输入的状态。
- `register()` 先验证输入，再把密码加密，防止明文入库。
- `User::create()` 把用户写入数据库。
- `Registered` 事件通常和邮箱验证等后续流程有关。
- `Auth::login($user)` 让新用户注册完直接登录，不需要再手动输入一次。
- 最后跳转到 Dashboard。

这一段的核心思想就是：先验证，再存库，再自动登录。

### 16.4 [resources/views/livewire/pages/auth/login.blade.php](resources/views/livewire/pages/auth/login.blade.php)

```php
new #[Layout('layouts.guest')] class extends Component
{
	public LoginForm $form;

	public function login(): void
	{
		$this->validate();

		$this->form->authenticate();

		Session::regenerate();

		$this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
	}
};
```

这一段负责把登录请求送到真正的认证逻辑里。

- `public LoginForm $form;` 表示这个页面把表单状态交给一个专门的表单对象管理。
- `$this->validate()` 先检查页面层绑定的数据有没有问题。
- `$this->form->authenticate()` 才是真正去比对用户名密码的动作。
- `Session::regenerate()` 是安全措施，登录成功后刷新会话，避免旧 session 被复用。
- `redirectIntended()` 会优先跳回用户原本想去的页面，如果没有，就回 Dashboard。

这个文件的重点不是“怎么认证”，而是“怎么把登录动作接到表单对象上”。

### 16.5 [app/Livewire/Forms/LoginForm.php](app/Livewire/Forms/LoginForm.php)

```php
public function authenticate(): void
{
	$this->ensureIsNotRateLimited();

	if (! Auth::attempt($this->only(['email', 'password']), $this->remember)) {
		RateLimiter::hit($this->throttleKey());

		throw ValidationException::withMessages([
			'form.email' => trans('auth.failed'),
		]);
	}

	RateLimiter::clear($this->throttleKey());
}

protected function ensureIsNotRateLimited(): void
{
	if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
		return;
	}

	event(new Lockout(request()));

	$seconds = RateLimiter::availableIn($this->throttleKey());

	throw ValidationException::withMessages([
		'form.email' => trans('auth.throttle', [
			'seconds' => $seconds,
			'minutes' => ceil($seconds / 60),
		]),
	]);
}
```

这一段是真正的登录校验逻辑。

- `ensureIsNotRateLimited()` 先看当前邮箱和 IP 有没有试错太多次。
- `Auth::attempt()` 才是 Laravel 的标准登录检查方法。
- 如果失败，`RateLimiter::hit()` 会记一次失败次数。
- 如果成功，`RateLimiter::clear()` 会把失败记录清掉。

你可以把它理解成：

“先查有没有被锁，再试密码，成功就清空失败记录，失败就报错。”

### 16.6 [resources/views/dashboard.blade.php](resources/views/dashboard.blade.php)

```blade
<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl text-gray-800 leading-tight">
			{{ __('Dashboard') }}
		</h2>
	</x-slot>

	<div class="py-12">
		<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
				@livewire('pages.dashboard.event-box', ['title' => 'Active Issues', 'type' => 'issues'])
				@livewire('pages.dashboard.event-box', ['title' => 'Upcoming Events', 'type' => 'events'])
			</div>
			<div class="mt-6">
				@livewire('pages.dashboard.calendar')
			</div>
		</div>
	</div>
</x-app-layout>
```

这一段的作用很简单：把页面骨架和组件拼起来。

- `x-app-layout` 是整个登录后页面的公共布局。
- `header` 里放页面标题。
- 上面两块 `event-box` 是事件摘要。
- 下面 `calendar` 是日历。

这说明 Dashboard 本身不是一个“塞满逻辑的页面”，它更像一个“组件容器”。

### 16.7 [app/Livewire/Pages/Dashboard/EventBox.php](app/Livewire/Pages/Dashboard/EventBox.php)

```php
public function mount($title = "All Events", $type = 'all', $limit = 4)
{
	$this->type = $type;
	$this->title = $title;
	$this->limit = $limit;

	// TODO: get db data
	$mockItems = [
		...
	];

	if ($this->type === 'events') {
		$filtered = array_filter($mockItems, fn ($item) => $item['type'] !== 'issue');
	} elseif ($this->type === 'issues') {
		$filtered = array_filter($mockItems, fn ($item) => $item['type'] === 'issue');
	} else {
		$filtered = $mockItems;
	}

	$this->items = array_values($filtered);

	usort($this->items, function ($a, $b) {
		return $a['date']->timestamp <=> $b['date']->timestamp;
	});

	$this->items = array_slice($this->items, 0, $this->limit);
}
```

这一段展示了一个很典型的“组件准备数据”的过程。

- `mount()` 会在组件初始化时执行。
- `$mockItems` 是模拟数据，说明这里还没接数据库。
- `type` 控制要显示哪类事件。
- `array_filter()` 用来过滤数据。
- `usort()` 用来按日期排序。
- `array_slice()` 只保留前几条。

如果你是初学者，可以先把这段理解成：

“先生成假数据，再按条件筛选，再排序，再截断。”

### 16.8 [app/Livewire/Pages/Dashboard/Calendar.php](app/Livewire/Pages/Dashboard/Calendar.php)

```php
public function mount()
{
	$this->events = [
		...
	];

	$this->dispatch('calendar-ready');
}
```

这一段说明日历的工作方式。

- `events` 保存日历事件数据。
- 这里的数据还是模拟值。
- `dispatch('calendar-ready')` 是告诉前端“数据已经准备好了，可以开始渲染”。

这就是 Livewire 和 JavaScript 协作的连接点。

### 16.9 [resources/js/calendar.js](resources/js/calendar.js)

```javascript
document.addEventListener("calendar-ready", function () {
    const calendarEl = document.getElementById("calendar");
    const events = JSON.parse(calendarEl.dataset.events);

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: "dayGridMonth",
        headerToolbar: {
            left: "prev,next,today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay",
        },
        events: events,
        eventClassNames: function (arg) {
            return [arg.event.extendedProps.type];
        },
    });

    calendar.render();
});
```

这一段是真正把日历显示出来的代码。

- 先监听 `calendar-ready`，说明它在等后端通知。
- `document.getElementById('calendar')` 找到页面里的日历容器。
- `JSON.parse(...)` 把后端传来的字符串变回数组。
- `new Calendar(...)` 创建 FullCalendar 实例。
- `eventClassNames` 让不同类型的事件可以有不同样式。

你可以把这一段理解成：

“后端发数据，前端拿数据画图。”

### 16.10 [resources/views/livewire/pages/admin/edit-user-roles.blade.php](resources/views/livewire/pages/admin/edit-user-roles.blade.php)

```blade
<select id="user" wire:model="selectedUserId" wire:change="selectUser">
	<option value="">-- Select User --</option>
	@foreach($users as $user)
		<option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
	@endforeach
</select>

@if($selectedUser)
	<p><strong>Current Role:</strong> {{ $selectedUser->roles->pluck('name')->join(', ') ?: __('(none)') }}</p>

	<x-primary-button wire:click="removeUserRole">
		{{ __('Remove Role') }}
	</x-primary-button>

	<select id="role" wire:model="selectedRoleName">
		<option value="">-- Select Role --</option>
		@foreach($roles as $role)
			<option value="{{ $role->name }}">{{ $role->name }}</option>
		@endforeach
	</select>

	<x-primary-button wire:click="updateUserRole">
		{{ __('Update Role') }}
	</x-primary-button>
@endif
```

这一段是管理员操作界面的核心。

- 先选用户。
- 选中后显示当前角色。
- 再选角色并更新。
- 也可以直接删除角色。

这段代码能帮助你理解 Livewire 的两个特点：

1. 表单项和 PHP 状态是同步的。
2. 按钮点击可以直接调用组件方法，不需要自己写很多 JavaScript。

### 16.11 [app/Livewire/Pages/Admin/EditUserRoles.php](app/Livewire/Pages/Admin/EditUserRoles.php)

```php
public function mount()
{
	$this->users = User::with('roles')->get();
	$this->roles = Role::all();
}

public function selectUser()
{
	$this->selectedUser = User::with('roles')->find($this->selectedUserId);
	$this->selectedRoleName = $this->selectedUser?->roles?->first()?->name;
}

public function updateUserRole()
{
	if (!$this->selectedUserId || !$this->selectedRoleName) {
		return;
	}

	$user = User::find($this->selectedUserId);

	if ($user) {
		$user->syncRoles([$this->selectedRoleName]);
		$this->selectedUser = $user->load('roles');
		$this->users = User::with('roles')->get();
	}
}
```

这一段是角色管理的后端逻辑。

- `mount()` 先拿到页面要显示的数据。
- `selectUser()` 在用户切换时刷新当前选择。
- `updateUserRole()` 用 `syncRoles()` 更新用户角色，并刷新页面列表。

重点不是这几个方法有多长，而是你要看出它们的职责分工：

- 初始化数据
- 选择用户
- 更新角色

### 16.12 [app/Models/User.php](app/Models/User.php)

```php
class User extends Authenticatable
{
	use HasApiTokens, HasFactory, Notifiable, HasRoles;

	protected $fillable = [
		'name',
		'email',
		'password',
	];

	protected $hidden = [
		'password',
		'remember_token',
	];

	protected function casts(): array
	{
		return [
			'email_verified_at' => 'datetime',
			'password' => 'hashed',
		];
	}
}
```

这一段说明用户模型具备三种能力：

- 可以当普通 Laravel 用户登录
- 可以持有 API token
- 可以拥有角色和权限

`fillable` 决定哪些字段可以批量写入。

`hidden` 决定哪些字段在返回 JSON 时不直接显示。

`casts()` 让字段自动转换成合适的类型，其中 `password => 'hashed'` 表示密码会自动以哈希形式保存。

### 16.13 [app/Policies/BasePolicy.php](app/Policies/BasePolicy.php)

```php
protected const ROLE_ALIASES = [
	'admin' => ['admin', 'Admin'],
	'manager' => ['manager', 'Maintenance Technician'],
	'technician' => ['technician', 'Calibration Specialist'],
	'guest' => ['guest', 'Guest'],
];

protected function hasAnyRole(User $user, array $roles): bool
{
	$expandedRoles = [];

	foreach ($roles as $role) {
		$expandedRoles = array_merge(
			$expandedRoles,
			self::ROLE_ALIASES[$role] ?? [$role],
		);
	}

	return $user->hasRole(array_values(array_unique($expandedRoles)));
}
```

这一段是权限系统的“翻译器”。

- `ROLE_ALIASES` 让代码里的规范角色名和数据库里的实际角色名互相兼容。
- `hasAnyRole()` 会把输入的角色展开，再交给 Spatie 去检查。

这样做的好处是：后面所有 Policy 文件都能用统一的角色写法。

### 16.14 [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php)

```php
User::firstOrCreate(
	['email' => 'test@example.com'],
	[
		'name' => 'Test User',
		'password' => Hash::make('password123'),
		'email_verified_at' => now(),
	]
);

$this->call(RoleSeeder::class);
```

这一段是数据库初始化入口。

- `firstOrCreate()` 的意思是“有就用，没有就创建”。
- 它先创建一个普通测试账号，方便你登录验证系统。
- 然后调用 `RoleSeeder` 继续创建角色和管理员账号。

### 16.15 [database/seeders/RoleSeeder.php](database/seeders/RoleSeeder.php)

```php
$roles = [
	'Admin',
	'Maintenance Technician',
	'Calibration Specialist'
];

foreach ($roles as $role) {
	Role::firstOrCreate(['name' => $role]);

	$user = User::firstOrCreate(
		['email' => 'admin@example.com'],
		['name' => 'Admin User', 'password' => '12345678']
	);

	$user->assignRole('Admin');
}
```

这一段负责“把系统最开始要用的角色和管理员账号建出来”。

- `Role::firstOrCreate()` 创建角色。
- `User::firstOrCreate()` 创建默认管理员。
- `assignRole('Admin')` 把管理员放进 Admin 角色里。

对初学者来说，这段代码很适合用来理解“程序怎么给自己准备第一批基础数据”。

### 16.16 [resources/js/app.js](resources/js/app.js) 和 [resources/css/app.css](resources/css/app.css)

```javascript
import "./bootstrap";
import "./calendar";
```

```css
@import "calendar.css";

@tailwind base;
@tailwind components;
@tailwind utilities;
```

这两段是前端入口最简洁的样子。

- `app.js` 负责把基础 JS 和日历 JS 一起加载。
- `app.css` 负责把日历样式和 Tailwind 样式合并起来。

你可以把它理解成“页面能不能正常看见和正常动起来”的总开关。

### 16.17 这一版你应该怎么读

如果你是初学者，我建议你按下面顺序读这一节：

1. 先看 [routes/web.php](routes/web.php) 和 [routes/auth.php](routes/auth.php)
2. 再看注册和登录页面
3. 再看 [app/Livewire/Forms/LoginForm.php](app/Livewire/Forms/LoginForm.php)
4. 再看 Dashboard 的三个文件
5. 再看角色管理的三个文件
6. 然后看 [app/Models/User.php](app/Models/User.php) 和 [app/Policies/BasePolicy.php](app/Policies/BasePolicy.php)
7. 最后看 Seeder 和前端入口

## 17. 登录后到 Dashboard 的完整流程图

下面这张图把“用户登录成功之后，系统怎么一步步把他带到 Dashboard，并把页面渲染出来”串起来了。你可以先看图，再回去对照前面的代码块。

```mermaid
flowchart TD
	A[访问登录页 /login] --> B[输入 email 和 password]
	B --> C[提交表单 wire:submit=login]
	C --> D[Login 页面先做表单验证]
	D --> E[调用 LoginForm.authenticate()]
	E --> F[先检查是否触发限流]
	F --> G{密码是否正确?}
	G -- 否 --> H[返回验证错误 auth.failed]
	H --> I[前端显示错误信息]
	G -- 是 --> J[清除失败记录 RateLimiter.clear]
	J --> K[Session::regenerate 刷新会话]
	K --> L[redirectIntended 跳转到 Dashboard]
	L --> M[进入 /dashboard]
	M --> N[web.php 检查 auth + verified]
	N --> O[渲染 dashboard.blade.php]
	O --> P[加载 EventBox 组件 2个]
	O --> Q[加载 Calendar 组件]
	Q --> R[Calendar 组件准备模拟 events]
	R --> S[dispatch calendar-ready]
	S --> T[resources/js/calendar.js 监听事件]
	T --> U[读取 dataset.events]
	U --> V[FullCalendar 渲染日历]
	P --> W[页面显示事件摘要]
	V --> X[页面显示完整日历]
```

### 17.1 这张图怎么读

- 上半段是“登录流程”。
- 下半段是“进入 Dashboard 之后页面如何渲染”。
- 中间最关键的分界点是 `Session::regenerate()` 和 `redirectIntended()`，它们把“认证成功”和“进入系统”连起来。
- `calendar-ready` 是后端和前端的接口信号，说明 Livewire 组件和 JavaScript 在这里开始配合。

### 17.2 你可以重点记住的几个节点

- `LoginForm.authenticate()`：真正验证账号密码的地方
- `Session::regenerate()`：登录成功后的安全刷新
- `web.php` 的 `auth + verified`：Dashboard 的访问门槛
- `EventBox`：显示页面上的摘要事件
- `Calendar` + `calendar.js`：把事件变成可视化日历

### 17.3 如果你想继续往下学

下一步最适合看的，是把这张图再拆成两张小图：

1. “登录页内部的认证流程图”
2. “Dashboard 内部的组件渲染流程图”

如果你愿意，我可以下一条继续给你画这两张更细的图。
