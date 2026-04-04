# Excel Data Integration Plan for Tester Register

# Tester Register Excel 数据集成计划

## TL;DR / 执行摘要

**English**

The uploaded Excel file contains real production data for 7 testers and 278 log records. This data should be converted into Laravel seeders so the team can populate the system with realistic records and reset the environment with `php artisan migrate:fresh --seed`.

**Recommended approach:** create 3 seeders: `TesterCustomerSeeder`, `TesterSeeder`, and `EventLogSeeder`, and store the Excel data as structured JSON inside the project.

**中文**

你上传的 Excel 文件包含 7 台 tester 和 278 条日志记录，这些都是真实生产数据。最合适的做法是把它转换成 Laravel Seeder，这样团队可以用真实记录来填充系统，并且可以通过 `php artisan migrate:fresh --seed` 一键重置环境。

**推荐方案：**创建 3 个 Seeder：`TesterCustomerSeeder`、`TesterSeeder`、`EventLogSeeder`，并把 Excel 数据转换成项目内的结构化 JSON。

---

## Data Mapping / 数据映射

### tester_register sheet / tester_register 表

**English**

The `tester_register` sheet should map to the `Tester` model, with some fields optionally supporting `TesterCustomer`.

| Excel column      | Database target | Field           | Notes                                                   |
| ----------------- | --------------- | --------------- | ------------------------------------------------------- |
| `tester_id`       | `testers`       | `id`            | Keep the original ID if needed for traceability         |
| `tester`          | `testers`       | `model`         | Main tester name/model                                  |
| `tester_no`       | `testers`       | `serial_number` | Unique field                                            |
| `customer_id`     | `testers`       | `customer_id`   | Foreign key; only 2 rows have values, the rest are `\N` |
| `tester_name_eng` | -               | -               | Optional reference field, not currently mapped          |
| `status`          | `testers`       | `status`        | Must fit `active`, `inactive`, or `maintenance`         |
| `location`        | `testers`       | `location`      | Physical location                                       |
| `impl_date`       | `testers`       | `purchase_date` | Use as the tester acquisition / implementation date     |
| `owner`           | -               | -               | No current database column                              |
| `prod_family`     | -               | -               | No current database column                              |
| `os`              | -               | -               | No current database column                              |
| `tester_type`     | -               | -               | No current database column                              |
| `prep`            | -               | -               | No current database column                              |
| `AssetNo1-5`      | -               | -               | No current database column                              |
| `price`           | -               | -               | No current database column                              |
| `npv_price`       | -               | -               | No current database column                              |

**中文**

`tester_register` 表应该映射到 `Tester` 模型，其中部分字段可以补充到 `TesterCustomer`。

| Excel 列          | 数据库目标 | 字段            | 说明                                         |
| ----------------- | ---------- | --------------- | -------------------------------------------- |
| `tester_id`       | `testers`  | `id`            | 如需可追溯性，可保留原始 ID                  |
| `tester`          | `testers`  | `model`         | tester 的主要名称/型号                       |
| `tester_no`       | `testers`  | `serial_number` | 唯一字段                                     |
| `customer_id`     | `testers`  | `customer_id`   | 外键；只有 2 行有值，其余为 `\N`             |
| `tester_name_eng` | -          | -               | 可作为参考字段，当前未映射                   |
| `status`          | `testers`  | `status`        | 需要符合 `active`、`inactive`、`maintenance` |
| `location`        | `testers`  | `location`      | 设备位置                                     |
| `impl_date`       | `testers`  | `purchase_date` | 可作为设备购置/导入日期                      |
| `owner`           | -          | -               | 当前数据库没有对应字段                       |
| `prod_family`     | -          | -               | 当前数据库没有对应字段                       |
| `os`              | -          | -               | 当前数据库没有对应字段                       |
| `tester_type`     | -          | -               | 当前数据库没有对应字段                       |
| `prep`            | -          | -               | 当前数据库没有对应字段                       |
| `AssetNo1-5`      | -          | -               | 当前数据库没有对应字段                       |
| `price`           | -          | -               | 当前数据库没有对应字段                       |
| `npv_price`       | -          | -               | 当前数据库没有对应字段                       |

### tester_log sheet / tester_log 表

**English**

The `tester_log` sheet maps best to the `EventLog` model.

| Excel column  | Database target | Field         | Notes                                                                   |
| ------------- | --------------- | ------------- | ----------------------------------------------------------------------- |
| `log_id`      | `event_logs`    | `id`          | Keep the original ID if desired                                         |
| `tester_id`   | `event_logs`    | `tester_id`   | Foreign key to `testers.id`                                             |
| `entry_date`  | `event_logs`    | `event_date`  | Log timestamp                                                           |
| `indication`  | `event_logs`    | `description` | Issue or event description                                              |
| `solution`    | -               | -             | No current database column; could be stored in notes or added later     |
| `detector`    | `event_logs`    | `recorded_by` | Person who recorded the log                                             |
| `solved_date` | -               | -             | No current database column; could be stored in notes or added later     |
| derived value | `event_logs`    | `type`        | Must be one of `maintenance`, `calibration`, `issue`, `repair`, `other` |

**中文**

`tester_log` 表最适合映射到 `EventLog` 模型。

| Excel 列      | 数据库目标   | 字段          | 说明                                                                   |
| ------------- | ------------ | ------------- | ---------------------------------------------------------------------- |
| `log_id`      | `event_logs` | `id`          | 如果需要可追溯性，可以保留原始 ID                                      |
| `tester_id`   | `event_logs` | `tester_id`   | 外键，关联 `testers.id`                                                |
| `entry_date`  | `event_logs` | `event_date`  | 日志时间                                                               |
| `indication`  | `event_logs` | `description` | 问题或事件描述                                                         |
| `solution`    | -            | -             | 当前没有对应字段，后续可放入 notes 或扩展字段                          |
| `detector`    | `event_logs` | `recorded_by` | 记录人                                                                 |
| `solved_date` | -            | -             | 当前没有对应字段，后续可放入 notes 或扩展字段                          |
| 推导值        | `event_logs` | `type`        | 必须属于 `maintenance`、`calibration`、`issue`、`repair`、`other` 之一 |

---

## Key Decision: Missing customer_id / 关键决策：customer_id 缺失问题

**English**

Five testers do not have a `customer_id` value. This conflicts with the current foreign key constraint.

Recommended options:

1. **Option A: create virtual customers**
    - Create a synthetic customer for each tester without a customer
    - Best for preserving referential integrity without changing migrations
    - This is the recommended approach

2. **Option B: allow `customer_id` to be nullable**
    - Update the migration and potentially some query logic
    - Best if the business wants to preserve the raw source data exactly as-is

3. **Option C: ask the data provider to fill in the missing customer information**
    - Best for accuracy, but slower

**Recommendation:** use Option A unless the missing customer information can be confirmed quickly.

**中文**

有 5 台 tester 没有 `customer_id`，这和当前外键约束不一致。

可选方案：

1. **方案 A：创建虚拟 customer**
    - 为没有 customer 的 tester 自动生成一个 customer
    - 最适合保持外键完整性，同时不改 migration
    - 这是推荐方案

2. **方案 B：允许 `customer_id` 为空**
    - 修改 migration，并可能需要调整一些查询逻辑
    - 如果业务要求严格保留原始数据，这种方式更贴近源文件

3. **方案 C：向数据提供方补齐 customer 信息**
    - 数据最准确，但耗时更长

**建议：**除非能很快补齐 customer 信息，否则优先用方案 A。

---

## Implementation Plan / 实施方案

### Step 1: Convert Excel to JSON / 第 1 步：将 Excel 转成 JSON

**English**

Create structured data files inside the project, for example:

- `database/seeders/data/testers.json`
- `database/seeders/data/event_logs.json`

Suggested structure:

```json
{
    "customers": [
        {
            "id": 1,
            "company_name": "Real Company A",
            "address": "Address...",
            "contact_person": "Person A",
            "phone": "123456",
            "email": "company@a.com"
        }
    ],
    "testers": [
        {
            "id": 1,
            "model": "Tester Model X",
            "serial_number": "TX-001",
            "customer_id": 1,
            "purchase_date": "2024-01-15",
            "status": "active",
            "location": "Lab A"
        }
    ]
}
```

```json
{
    "events": [
        {
            "id": 1,
            "tester_id": 1,
            "type": "issue",
            "description": "Detection problem found",
            "event_date": "2025-01-10 14:30:00",
            "recorded_by": "Technician Name"
        }
    ]
}
```

**中文**

建议在项目中创建结构化数据文件，例如：

- `database/seeders/data/testers.json`
- `database/seeders/data/event_logs.json`

建议结构如下：

```json
{
    "customers": [
        {
            "id": 1,
            "company_name": "Real Company A",
            "address": "Address...",
            "contact_person": "Person A",
            "phone": "123456",
            "email": "company@a.com"
        }
    ],
    "testers": [
        {
            "id": 1,
            "model": "Tester Model X",
            "serial_number": "TX-001",
            "customer_id": 1,
            "purchase_date": "2024-01-15",
            "status": "active",
            "location": "Lab A"
        }
    ]
}
```

```json
{
    "events": [
        {
            "id": 1,
            "tester_id": 1,
            "type": "issue",
            "description": "Detection problem found",
            "event_date": "2025-01-10 14:30:00",
            "recorded_by": "Technician Name"
        }
    ]
}
```

---

### Step 2: Create TesterCustomerSeeder / 第 2 步：创建 TesterCustomerSeeder

**English**

This seeder creates customer records from the JSON file.

**中文**

这个 seeder 用来从 JSON 文件创建 customer 记录。

---

### Step 3: Create TesterSeeder / 第 3 步：创建 TesterSeeder

**English**

This seeder creates tester records and resolves missing customers by generating virtual customers when needed.

**中文**

这个 seeder 用来创建 tester 记录，并在必要时为缺失的 customer 自动生成虚拟 customer。

---

### Step 4: Create EventLogSeeder / 第 4 步：创建 EventLogSeeder

**English**

This seeder creates the 278 event log rows from the `tester_log` data.

**中文**

这个 seeder 用来把 `tester_log` 的 278 条数据导入到 `EventLog`。

---

### Step 5: Update DatabaseSeeder / 第 5 步：修改 DatabaseSeeder

**English**

Update `database/seeders/DatabaseSeeder.php` so that it calls the new seeders in the correct order:

1. `RoleSeeder`
2. `TesterCustomerSeeder`
3. `TesterSeeder`
4. `EventLogSeeder`

**中文**

更新 `database/seeders/DatabaseSeeder.php`，按正确顺序调用新 Seeder：

1. `RoleSeeder`
2. `TesterCustomerSeeder`
3. `TesterSeeder`
4. `EventLogSeeder`

---

## File Plan / 文件规划

| File                                              | English purpose                            | 中文用途                          |
| ------------------------------------------------- | ------------------------------------------ | --------------------------------- |
| `database/seeders/data/testers.json`              | Structured tester and customer source data | tester 与 customer 的结构化源数据 |
| `database/seeders/data/event_logs.json`           | Structured event log source data           | event log 的结构化源数据          |
| `database/seeders/TesterCustomerSeeder.php`       | Seed customer records                      | 导入 customer 记录                |
| `database/seeders/TesterSeeder.php`               | Seed tester records                        | 导入 tester 记录                  |
| `database/seeders/EventLogSeeder.php`             | Seed event logs                            | 导入日志记录                      |
| `database/seeders/DatabaseSeeder.php`             | Orchestrate all seeders                    | 统一调度所有 seeder               |
| `database/migrations/...create_testers_table.php` | Optional nullable FK change                | 如需可选修改外键可空              |

---

## Validation Checklist / 验证清单

**English**

- Excel to JSON conversion is complete and valid
- Each tester has a valid `model`, `serial_number`, `status`, and `location`
- `serial_number` values are unique
- Every `event_log.tester_id` matches an existing tester
- `php artisan migrate:fresh --seed` runs successfully
- Tester list API returns the expected records
- Event log pages or API endpoints show the imported real data

**中文**

- Excel 已成功转换成 JSON，并且格式正确
- 每条 tester 都有有效的 `model`、`serial_number`、`status`、`location`
- `serial_number` 没有重复
- 每条 `event_log.tester_id` 都能找到对应 tester
- `php artisan migrate:fresh --seed` 可以顺利执行
- tester 列表 API 能返回预期记录
- 日志页面或 API 能显示导入后的真实数据

---

## Next Questions / 下一步确认

**English**

1. Is the virtual customer approach acceptable?
2. Should the JSON files live under `database/seeders/data/`?
3. Should `EventLog.type` default to `issue`, or do you want a rule-based mapping?
4. Do you also want sample Fixture, MaintenanceSchedule, or CalibrationSchedule data?

**中文**

1. 虚拟 customer 方案可以接受吗？
2. JSON 文件放在 `database/seeders/data/` 可以吗？
3. `EventLog.type` 要统一设成 `issue`，还是希望做规则映射？
4. 是否也要补充 Fixture、MaintenanceSchedule、CalibrationSchedule 的示例数据？
