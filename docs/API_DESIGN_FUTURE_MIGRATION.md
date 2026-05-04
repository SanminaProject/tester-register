# Tester Register API Design – Future Migration Strategy

## Executive Summary

This document describes a strategic roadmap for migrating the Tester Register web frontend from server-side Livewire + Eloquent directly access to an **API-first architecture** consuming the existing `/api/v1` REST API.

**Key Outcomes**:

- Web frontend becomes a JavaScript SPA (Single Page Application)
- Data flow: Browser → API → Database (instead of: Browser → Livewire → Database)
- Backend API remains unchanged—only frontend consumption pattern changes
- Migration occurs incrementally (page-by-page) to minimize risk
- Existing external API consumers continue working without modification

**Timeline**: Estimated 12–18 weeks for full migration (depending on frontend team size and complexity of page logic).

---

## 1. Current State vs. Future State

### 1.1 Current Architecture (Today)

```
┌─────────────────────────────────┐
│     Web Browser (User)          │
│  ┌─────────────────────────────┐│
│  │   Livewire Volt Pages       ││
│  │ (Server-side rendered)      ││
│  └──────────────┬──────────────┘│
└─────────────────┼───────────────┘
                  │
        HTTP request (Form POST,
        Livewire method call)
                  │
        ┌─────────▼──────────┐
        │ Livewire Component │
        │  Server-side Logic │
        ├─────────────────────┤
        │ Direct Eloquent     │
        │  Model::find()      │
        │  Model::query()     │
        └─────────────────────┘
                  │
        ┌─────────▼──────────┐
        │   MySQL Database   │
        └────────────────────┘

Issues:
• Frontend & backend tightly coupled
• UI refresh requires full page reload
• Mobile-style interactions are awkward
• Offline data sync impossible
• Code duplication in authorization logic
```

### 1.2 Future Architecture (Post-Migration)

```
┌─────────────────────────────────┐
│     Web Browser (User)          │
│  ┌─────────────────────────────┐│
│  │   JavaScript SPA            ││
│  │ (React, Vue, or similar)    ││
│  │ ┌─────────────────────────┐ ││
│  │ │ API Client Module       │ ││
│  │ │ (Axios/Fetch)          │ ││
│  │ │ • Token management      │ ││
│  │ │ • Request interceptors  │ ││
│  │ │ • Automatic retries     │ ││
│  │ │ • Caching layer         │ ││
│  │ └─────────────────────────┘ ││
│  └──────────────┬───────────────┘│
└─────────────────┼─────────────────┘
                  │
        HTTP requests (JSON REST)
        Authorization: Bearer <token>
                  │
        ┌─────────▼──────────────┐
        │  /api/v1 Endpoints     │
        │ (Controllers)          │
        ├────────────────────────┤
        │ Policy Authorization   │
        │ Request Validation     │
        │ Response Formatting    │
        └─────────────────────────┘
                  │
        ┌─────────▼──────────┐
        │   MySQL Database   │
        └────────────────────┘

Benefits:
• Loose coupling (SPA ↔ API)
• Dynamic UI updates (no full reload)
• Better offline support (with IndexedDB)
• Reusable API client (mobile + web)
• Clear separation of concerns
• Easy to test frontend independently
```

---

## 2. Migration Phases

### 2.1 Phase 0: Preparation (Weeks 1–2)

**Goal**: Set up foundational infrastructure for the SPA and API client.

#### 2.1.1 Frontend Tooling Setup

**Action**: Establish SPA framework (choose one):

- **Option A** (Recommended): Vue 3 with TypeScript + Vite (aligns with Laravel ecosystem)
- **Option B**: React 18 + TypeScript + Vite
- **Option C**: Svelte + TypeScript + Vite

**Code Steps**:

```bash
# Using Vite with Vue (already in tailwind.config.js)
npm install
npm run dev

# Create directory structure
src/
  components/      # Reusable Vue components
  pages/           # Page-level components
  services/        # API client modules
  stores/          # State management (Pinia or Vuex)
  types/           # TypeScript interfaces
  utils/           # Helpers
```

**Code Reference**: [tailwind.config.js](../tailwind.config.js), [vite.config.js](../vite.config.js) already configured.

#### 2.1.2 API Client Module

**Create**: `src/services/api.ts` (TypeScript)

```typescript
// src/services/api.ts
import axios, { AxiosInstance } from "axios";

interface ApiResponse<T> {
    success: boolean;
    message: string;
    data: T;
    code: number;
    errors?: Record<string, string[]>;
}

class ApiClient {
    private client: AxiosInstance;
    private token: string | null = null;

    constructor(baseURL: string = "/api/v1") {
        this.client = axios.create({
            baseURL,
            headers: {
                "Content-Type": "application/json",
            },
        });

        // Request interceptor: attach token
        this.client.interceptors.request.use((config) => {
            if (this.token) {
                config.headers.Authorization = `Bearer ${this.token}`;
            }
            return config;
        });

        // Response interceptor: handle errors
        this.client.interceptors.response.use(
            (response) => response,
            (error) => {
                if (error.response?.status === 401) {
                    // Token expired, redirect to login
                    window.location.href = "/login";
                }
                return Promise.reject(error);
            },
        );

        // Load token from localStorage on init
        this.token = localStorage.getItem("auth_token");
    }

    async login(
        email: string,
        password: string,
    ): Promise<ApiResponse<LoginData>> {
        const response = await this.client.post<ApiResponse<LoginData>>(
            "/auth/login",
            { email, password },
        );
        if (response.data.success && response.data.data.token) {
            this.setToken(response.data.data.token);
        }
        return response.data;
    }

    setToken(token: string): void {
        this.token = token;
        localStorage.setItem("auth_token", token);
    }

    clearToken(): void {
        this.token = null;
        localStorage.removeItem("auth_token");
    }

    // Resource methods (auto-generated for each endpoint)
    async getTesterCustomers(page: number = 1, search?: string) {
        return this.client.get<ApiResponse<PaginatedResponse<TesterCustomer>>>(
            "/tester-customers",
            { params: { page, search } },
        );
    }

    async createTesterCustomer(data: CreateTesterCustomerPayload) {
        return this.client.post<ApiResponse<TesterCustomer>>(
            "/tester-customers",
            data,
        );
    }

    // ... more methods for each resource
}

export default new ApiClient();
```

**Benefits**:

- Centralized token management
- Automatic request/response transformation
- Error handling with retry logic
- TypeScript support for type safety

#### 2.1.3 State Management Setup

**Choose**: Pinia (recommended for Vue 3) or Vuex

```typescript
// src/stores/authStore.ts (Pinia)
import { defineStore } from "pinia";
import apiClient from "@/services/api";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        token: localStorage.getItem("auth_token") || null,
        user: null,
        roles: [],
    }),

    actions: {
        async login(email: string, password: string) {
            const response = await apiClient.login(email, password);
            if (response.success) {
                this.token = response.data.token;
                this.user = response.data.user;
                this.roles = response.data.roles;
            }
            return response;
        },

        logout() {
            this.token = null;
            this.user = null;
            this.roles = [];
            apiClient.clearToken();
        },
    },

    getters: {
        isAuthenticated: (state) => !!state.token,
        hasRole: (state) => (role: string) => state.roles.includes(role),
    },
});
```

#### 2.1.4 Environment Configuration

**Create**: `.env.local` (for local development)

```
VITE_API_URL=http://localhost:8000/api/v1
VITE_APP_TITLE=Tester Register
```

**Consume in API client**:

```typescript
const baseURL = import.meta.env.VITE_API_URL || "/api/v1";
```

**Estimated Effort**: 1–2 weeks (one frontend developer).

---

### 2.2 Phase 1: Auth & Login Page (Weeks 3–4)

**Goal**: Implement SPA authentication and login page using API tokens.

#### 2.2.1 Login Page Component

**File**: `src/pages/Login.vue`

```vue
<template>
    <div class="login-container">
        <h1>Tester Register - Login</h1>
        <form @submit.prevent="handleLogin">
            <input v-model="email" type="email" placeholder="Email" required />
            <input
                v-model="password"
                type="password"
                placeholder="Password"
                required
            />
            <button type="submit" :disabled="loading">
                {{ loading ? "Logging in..." : "Login" }}
            </button>
            <p v-if="error" class="error">{{ error }}</p>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/authStore";

const email = ref("");
const password = ref("");
const error = ref("");
const loading = ref(false);

const router = useRouter();
const authStore = useAuthStore();

async function handleLogin() {
    loading.value = true;
    error.value = "";

    try {
        const response = await authStore.login(email.value, password.value);
        if (response.success) {
            router.push("/dashboard");
        } else {
            error.value = response.message || "Login failed";
        }
    } catch (err: any) {
        error.value = err.response?.data?.message || "An error occurred";
    } finally {
        loading.value = false;
    }
}
</script>
```

#### 2.2.2 Router Setup

**File**: `src/router/index.ts`

```typescript
import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/authStore";

const routes = [
    { path: "/login", component: () => import("@/pages/Login.vue") },
    {
        path: "/dashboard",
        component: () => import("@/pages/Dashboard.vue"),
        meta: { requiresAuth: true },
    },
    // More routes to be added in later phases
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next("/login");
    } else if (to.path === "/login" && authStore.isAuthenticated) {
        next("/dashboard");
    } else {
        next();
    }
});

export default router;
```

#### 2.2.3 API Endpoint Verification

**Test**: Confirm `/api/v1/auth/login` and `/api/v1/auth/register` work with token response.

**Command**:

```bash
php artisan test tests/Feature/Api/ApiSmokeTest.php --filter=login
```

**Expected**: Auth endpoints return `{token, user, roles}` as documented in [API_DESIGN_CURRENT_STATE.md#23-auth-response](./API_DESIGN_CURRENT_STATE.md#23-auth-response).

**Estimated Effort**: 1–2 weeks.

---

### 2.3 Phase 2: Core Resource Pages (Weeks 5–10)

**Goal**: Migrate the most-used pages to consume API. Recommended priority order:

#### 2.3.1 Migration Priority (Suggested Order)

1. **Testers List & CRUD** (Phase 2a, Week 5–6)
    - Most frequently used page
    - Relatively simple CRUD
    - Good test vehicle for pagination & filtering

2. **Tester Customers List & CRUD** (Phase 2b, Week 6–7)
    - Required before creating testers
    - Simple form, standard CRUD

3. **Fixtures List & CRUD** (Phase 2c, Week 7–8)
    - Moderate complexity (belongs to tester)
    - Good practice for nested resources

4. **Maintenance Schedules** (Phase 2d, Week 8–9)
    - Includes custom action (`/complete`)
    - Event log creation on completion
    - Moderate complexity

5. **Calibration Schedules** (Phase 2e, Week 9–10)
    - Same pattern as maintenance
    - Can reuse components

#### 2.3.2 Example: Testers Page Migration

**Current Livewire**: [app/Livewire/Pages/Testers/Index.php](../app/Livewire/Pages/Testers/Index.php)

**New SPA Component**: `src/pages/Testers/Index.vue`

```vue
<template>
    <div class="testers-page">
        <h1>Testers</h1>
        <div class="filters">
            <input
                v-model="search"
                placeholder="Search..."
                @input="fetchTesters"
            />
            <select v-model="statusFilter" @change="fetchTesters">
                <option value="">All</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Model</th>
                    <th>Serial</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="tester in testers" :key="tester.id">
                    <td>{{ tester.id }}</td>
                    <td>{{ tester.model }}</td>
                    <td>{{ tester.serial_number }}</td>
                    <td>{{ tester.status }}</td>
                    <td>
                        <button @click="editTester(tester.id)">Edit</button>
                        <button @click="deleteTester(tester.id)">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="pagination">
            <button
                v-for="page in totalPages"
                :key="page"
                :class="{ active: currentPage === page }"
                @click="
                    currentPage = page;
                    fetchTesters();
                "
            >
                {{ page }}
            </button>
        </div>

        <TesterFormModal
            v-if="showForm"
            :tester="selectedTester"
            @save="saveTester"
            @close="showForm = false"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { useAuthStore } from "@/stores/authStore";
import apiClient from "@/services/api";
import TesterFormModal from "@/components/TesterFormModal.vue";

interface Tester {
    id: number;
    customer_id: number;
    model: string;
    serial_number: string;
    status: "active" | "inactive" | "maintenance";
    // ... other fields
}

const testers = ref<Tester[]>([]);
const search = ref("");
const statusFilter = ref("");
const currentPage = ref(1);
const perPage = 15;
const totalPages = ref(1);
const selectedTester = ref<Tester | null>(null);
const showForm = ref(false);

const authStore = useAuthStore();

async function fetchTesters() {
    try {
        const response = await apiClient.getTesters({
            page: currentPage.value,
            per_page: perPage,
            search: search.value,
            status: statusFilter.value,
        });

        if (response.data.success) {
            testers.value = response.data.data.items;
            totalPages.value = response.data.data.pagination.last_page;
        }
    } catch (error) {
        console.error("Failed to fetch testers:", error);
    }
}

async function saveTester(testerData: Partial<Tester>) {
    try {
        if (selectedTester.value?.id) {
            await apiClient.updateTester(selectedTester.value.id, testerData);
        } else {
            await apiClient.createTester(testerData);
        }
        showForm.value = false;
        fetchTesters();
    } catch (error) {
        console.error("Failed to save tester:", error);
    }
}

async function deleteTester(id: number) {
    if (confirm("Are you sure?")) {
        try {
            await apiClient.deleteTester(id);
            fetchTesters();
        } catch (error) {
            console.error("Failed to delete tester:", error);
        }
    }
}

function editTester(id: number) {
    selectedTester.value = testers.value.find((t) => t.id === id) || null;
    showForm.value = true;
}

onMounted(() => {
    fetchTesters();
});
</script>

<style scoped>
/* Tailwind classes already included in template */
</style>
```

**Key Points**:

- API client methods (`getTesters`, `createTester`, `updateTester`, `deleteTester`) called from component
- Pagination handled on frontend
- Error handling with user feedback
- Form modal for create/edit
- Re-fetch after mutations

#### 2.3.3 API Client Method Expansion

**Extend**: `src/services/api.ts` with all resource methods

```typescript
// Full API client methods for CRUD + custom actions
class ApiClient {
    // Testers
    async getTesters(params: any) {
        return this.client.get("/testers", { params });
    }

    async getTester(id: number) {
        return this.client.get(`/testers/${id}`);
    }

    async createTester(data: any) {
        return this.client.post("/testers", data);
    }

    async updateTester(id: number, data: any) {
        return this.client.patch(`/testers/${id}`, data);
    }

    async updateTesterStatus(id: number, status: string) {
        return this.client.patch(`/testers/${id}/status`, { status });
    }

    async deleteTester(id: number) {
        return this.client.delete(`/testers/${id}`);
    }

    // Maintenance Schedules
    async getMaintenanceSchedules(params: any) {
        return this.client.get("/maintenance-schedules", { params });
    }

    async completeMaintenanceSchedule(id: number, data: any) {
        return this.client.post(`/maintenance-schedules/${id}/complete`, data);
    }

    // ... similar for other resources
}
```

#### 2.3.4 Testing During Migration

**Create**: `tests/Feature/Frontend/TesterPageTest.ts` (Playwright/Cypress)

```typescript
// E2E test: Verify Testers page works end-to-end
test("User can view testers list", async ({ page }) => {
    await page.goto("/login");
    await page.fill('input[type="email"]', "admin@example.com");
    await page.fill('input[type="password"]', "12345678");
    await page.click('button:has-text("Login")');

    await page.goto("/testers");
    const table = await page.locator("table tbody tr");
    expect(await table.count()).toBeGreaterThan(0);
});
```

**Estimated Effort**: 5–6 weeks (two frontend developers, one backend developer for API fixes).

---

### 2.4 Phase 3: Advanced Pages (Weeks 11–14)

**Goal**: Migrate remaining pages with complex logic (event logs, reporting, etc.).

#### 2.4.1 Event Logs Page

**Pattern**: List with advanced filtering + sorting

```typescript
// API call with complex params
async getEventLogs(params: {
    page?: number;
    type?: string;
    date_from?: string;
    date_to?: string;
    tester_id?: number;
}) {
    return this.client.get('/event-logs', { params });
}
```

#### 2.4.2 Spare Parts Inventory

**Pattern**: List with stock status filtering + dynamic updates

```typescript
// Stock status is computed on backend
async getSparePartsWithStock(stock_status?: 'low' | 'normal' | 'full') {
    return this.client.get('/spare-parts', {
        params: { stock_status }
    });
}
```

#### 2.4.3 Reporting & Analytics (if applicable)

**Consideration**: If current Livewire pages include reports/dashboards, create new API endpoints or aggregate data client-side using existing endpoints.

**Estimated Effort**: 3–4 weeks.

---

### 2.5 Phase 4: Optimization & Cleanup (Weeks 15–18)

**Goal**: Performance tuning, edge case handling, and cleanup of old Livewire code.

#### 2.5.1 Client-Side Caching

**Add**: Caching layer to API client to reduce redundant requests

```typescript
class CachedApiClient extends ApiClient {
    private cache = new Map<string, { data: any; timestamp: number }>();
    private cacheTimeout = 5 * 60 * 1000; // 5 minutes

    async getTesters(params: any) {
        const cacheKey = `testers_${JSON.stringify(params)}`;
        const cached = this.cache.get(cacheKey);

        if (cached && Date.now() - cached.timestamp < this.cacheTimeout) {
            return { data: cached.data };
        }

        const response = await super.getTesters(params);
        this.cache.set(cacheKey, {
            data: response.data,
            timestamp: Date.now(),
        });

        return response;
    }

    invalidateCache(pattern?: string) {
        if (pattern) {
            for (const [key] of this.cache) {
                if (key.includes(pattern)) {
                    this.cache.delete(key);
                }
            }
        } else {
            this.cache.clear();
        }
    }
}
```

#### 2.5.2 Error Handling & Retry Logic

**Add**: Automatic retry for transient network failures

```typescript
async function apiCallWithRetry(fn: () => Promise<any>, maxRetries = 3) {
    for (let i = 0; i < maxRetries; i++) {
        try {
            return await fn();
        } catch (error: any) {
            if (i === maxRetries - 1 || error.response?.status === 401) {
                throw error;
            }
            await new Promise((resolve) => setTimeout(resolve, 1000 * (i + 1)));
        }
    }
}
```

#### 2.5.3 Loading States & Optimistic Updates

**Pattern**: Show immediate UI feedback while API request completes

```vue
<script setup>
async function updateTester(id: number, data: any) {
    // Optimistic update
    const index = testers.value.findIndex((t) => t.id === id);
    const original = { ...testers.value[index] };
    testers.value[index] = { ...testers.value[index], ...data };

    try {
        await apiClient.updateTester(id, data);
    } catch (error) {
        // Revert on failure
        testers.value[index] = original;
        showError('Failed to update tester');
    }
}
</script>
```

#### 2.5.4 Livewire Component Cleanup

**Action**: After all pages migrated to SPA:

1. Remove Livewire components from `app/Livewire/Pages/`
2. Keep Livewire infrastructure only for admin/config pages (if any remain)
3. Archive old routes in `routes/web.php`

**Estimated Effort**: 3–4 weeks (performance tuning + testing + cleanup).

---

## 3. Breaking Changes & Backward Compatibility

### 3.1 No Backend Changes Required

The existing API endpoints require **no modifications** during frontend migration. All endpoints remain compatible with external consumers.

**Guarantee**: External API clients (Postman, mobile apps, third-party integrations) continue working without any code changes.

### 3.2 Frontend Breaking Changes (None Expected)

The new SPA frontend uses the same API contract; no breaking changes introduced.

### 3.3 Deprecated Livewire Routes (If Any)

Once Livewire pages are fully migrated, consider:

- Redirecting old Livewire routes to new SPA routes
- Documenting the transition in release notes
- Providing a migration guide for any custom integrations

---

## 4. Data Synchronization & Real-Time Updates

### 4.1 Current Limitations (No Real-Time Sync)

In the current Livewire-based system, if one user makes a change via the API, other users' browsers won't see the update. This is acceptable because:

- Web frontend is the primary interface (external API use is secondary)
- Users don't typically have multiple browser tabs open to the same page
- Existing external API consumers (if any) are fine with eventual consistency

### 4.2 Future Enhancement: WebSocket Broadcasting (Optional)

If real-time multi-user synchronization becomes critical, add Laravel Reverb or Socket.io:

```typescript
// Example: Listen for tester updates
window.Echo.channel(`testers.${testerId}`).listen(
    "TesterUpdated",
    (event: any) => {
        // Refresh tester data
        fetchTesters();
    },
);
```

**Effort**: 2–3 weeks (if implemented later).

---

## 5. Authentication & Token Management

### 5.1 Token Storage

**Recommendation**: Store tokens in memory (sessionStorage) with automatic refresh.

```typescript
// Option A: sessionStorage (cleared on browser close)
sessionStorage.setItem("auth_token", token);

// Option B: localStorage (persists across sessions)
localStorage.setItem("auth_token", token);
// SECURITY NOTE: Only use localStorage if HTTPS is enforced
```

### 5.2 Token Refresh Strategy

Current API lacks refresh token endpoint. Implement one for better security:

```typescript
// app/Http/Controllers/Api/AuthController.php (add method)
public function refresh(Request $request)
{
    $token = $request->user()->createToken('api-token')->plainTextToken;
    return response()->json([
        'success' => true,
        'data' => ['token' => $token],
    ]);
}

// routes/api.php
Route::post('auth/refresh', [AuthController::class, 'refresh'])
    ->middleware('auth:sanctum');
```

**Estimated Effort to Implement**: 1 week.

### 5.3 Logout

Current logout endpoint exists:

```
POST /api/v1/auth/logout
Authorization: Bearer <token>
```

**Frontend Handler**:

```typescript
async function logout() {
    try {
        await apiClient.logout();
    } finally {
        // Clear local storage regardless of API response
        authStore.logout();
        router.push("/login");
    }
}
```

---

## 6. Error Handling & User Feedback

### 6.1 API Error Codes

**Map backend errors to frontend user messages**:

```typescript
function getErrorMessage(code: number, message: string): string {
    switch (code) {
        case 401:
            return "Your session has expired. Please log in again.";
        case 403:
            return "You do not have permission to perform this action.";
        case 404:
            return "The requested resource was not found.";
        case 422:
            return "Please check your input and try again.";
        case 429:
            return "Too many requests. Please wait a moment and try again.";
        case 500:
            return "A server error occurred. Please contact support.";
        default:
            return message || "An unexpected error occurred.";
    }
}
```

### 6.2 Validation Error Handling

**Display field-level errors from API**:

```vue
<template>
    <form @submit.prevent="save">
        <div class="form-group">
            <input v-model="form.email" type="email" placeholder="Email" />
            <p v-if="errors.email" class="error">{{ errors.email[0] }}</p>
        </div>
        <button type="submit" :disabled="loading">Save</button>
    </form>
</template>

<script setup>
const errors = ref({});

async function save() {
    try {
        await apiClient.updateTester(form.id, form);
    } catch (error: any) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        }
    }
}
</script>
```

---

## 7. Testing Strategy

### 7.1 API Contract Tests (Already Exist)

**Location**: [tests/Feature/Api/](../tests/Feature/Api/)

**Run Before Each Frontend Release**:

```bash
php artisan test tests/Feature/Api/
```

**Ensures**: Frontend changes don't break API contract.

### 7.2 Frontend Unit Tests (New)

**Framework**: Vitest (Vue 3 compatible)

```typescript
// tests/unit/services/api.test.ts
import { describe, it, expect, vi } from "vitest";
import ApiClient from "@/services/api";

describe("ApiClient", () => {
    it("should attach token to requests", async () => {
        const apiClient = new ApiClient();
        apiClient.setToken("test-token");

        // Mock axios
        const spy = vi.spyOn(apiClient["client"], "get");

        await apiClient.getTesters({ page: 1 });

        expect(spy).toHaveBeenCalledWith(
            "/testers",
            expect.objectContaining({
                headers: expect.objectContaining({
                    Authorization: "Bearer test-token",
                }),
            }),
        );
    });
});
```

### 7.3 Frontend E2E Tests (New)

**Framework**: Playwright or Cypress

```typescript
// tests/e2e/testers.spec.ts
import { test, expect } from "@playwright/test";

test("User can create a tester", async ({ page }) => {
    // Login
    await page.goto("/login");
    await page.fill('input[type="email"]', "admin@example.com");
    await page.fill('input[type="password"]', "12345678");
    await page.click('button:has-text("Login")');

    // Navigate to testers
    await page.goto("/testers");
    await page.click('button:has-text("New Tester")');

    // Fill form
    await page.fill('input[name="model"]', "DMM-3000");
    await page.fill('input[name="serial_number"]', "SN-99999");
    await page.selectOption('select[name="status"]', "active");

    // Submit
    await page.click('button:has-text("Save")');

    // Verify
    await expect(page.locator("text=DMM-3000")).toBeVisible();
});
```

---

## 8. Performance Considerations

### 8.1 API Response Optimization

**Current**: Responses include all fields; pagination is 15 items per page.

**Frontend Optimization**:

- Request only needed fields using sparse fieldsets (if API updated to support)
- Increase pagination size based on network conditions
- Lazy-load related data (e.g., customer details on demand)

### 8.2 Client-Side Caching

**Strategy**:

- Cache GET requests (with 5–10 minute TTL)
- Invalidate cache on mutations (POST, PATCH, DELETE)
- Provide manual cache refresh button for users

### 8.3 Network Optimization

**Techniques**:

- Bundle splitting (lazy load pages)
- Image optimization (if needed)
- Minification (automatic via Vite)
- Gzip compression (enable in web server)

---

## 9. Migration Rollback Plan

### 9.1 Parallel Running

During Phase 2–3, keep both Livewire and SPA code running:

- Livewire routes at `/`
- SPA routes at `/app/`
- Allow users to switch during beta testing

### 9.2 Feature Flags

Conditionally enable SPA pages for testing:

```typescript
// config/features.php
return [
    'spa_testers' => env('FEATURE_SPA_TESTERS', false),
    'spa_fixtures' => env('FEATURE_SPA_FIXTURES', false),
];

// routes/web.php
if (config('features.spa_testers')) {
    Route::get('/testers', ...) // SPA route
} else {
    Route::get('/testers', ...) // Livewire route
}
```

### 9.3 Quick Rollback

If SPA version has critical bugs:

1. Set feature flags to false
2. Users automatically revert to Livewire version
3. Fix bugs in SPA code
4. Re-enable when ready

---

## 10. Communication & Training

### 10.1 Stakeholder Notifications

**Week 1**: Announce migration plan, timeline, and benefits.

**Sample Message**:

> "We're upgrading the Tester Register web interface to a modern, responsive application. The backend API remains unchanged, and external integrations are unaffected. Rollout occurs incrementally starting in Week 5."

### 10.2 User Training

**For end users**:

- Record short demo videos of new UI
- Provide side-by-side before/after screenshots
- Set up beta group for early feedback

**For admins/integrators**:

- Confirm API contract is unchanged
- Share this document ([API_DESIGN_CURRENT_STATE.md](./API_DESIGN_CURRENT_STATE.md))
- Provide test environment for validation

### 10.3 Documentation Updates

**Create**: New developer docs for SPA

- [FRONTEND_ARCHITECTURE.md](../docs/FRONTEND_ARCHITECTURE.md) – SPA structure, routing, state management
- [API_CLIENT_GUIDE.md](../docs/API_CLIENT_GUIDE.md) – How to use ApiClient module
- [MIGRATION_GUIDE.md](../docs/MIGRATION_GUIDE.md) – Step-by-step for moving existing code

---

## 11. Cost & Resource Estimation

### 11.1 Team Composition

- **1 Frontend Lead** (Vue/React expert, architecture decisions)
- **2 Frontend Developers** (component development, testing)
- **1 Backend Developer** (API fixes, token refresh endpoint, WebSocket if needed)
- **1 QA Engineer** (end-to-end testing)

### 11.2 Timeline Breakdown

| Phase       | Duration     | Focus                                   | Team                     |
| ----------- | ------------ | --------------------------------------- | ------------------------ |
| 0: Prep     | 2 weeks      | Tooling, API client, state mgmt         | 1 FE Lead + 1 FE Dev     |
| 1: Auth     | 2 weeks      | Login, auth flow                        | 1 FE Dev + 0.5 BE Dev    |
| 2: CRUD     | 6 weeks      | Testers, customers, fixtures, schedules | 2 FE Dev + 1 BE Dev + QA |
| 3: Advanced | 4 weeks      | Event logs, reports, edge cases         | 2 FE Dev + QA            |
| 4: Optimize | 4 weeks      | Performance, caching, cleanup, testing  | All                      |
| **Total**   | **18 weeks** | Full SPA migration                      | 1–4 people               |

### 11.3 Cost Estimate (Rough)

Assuming $80/hour contractor rate:

- 4 people × 18 weeks × 40 hours/week × $80/hour = **$230,400**
- Varies by location, expertise, and parallelization

---

## 12. Contingencies & Risk Mitigation

### 12.1 Risk: API Endpoints Have Bugs

**Mitigation**: Run [tests/Feature/Api/](../tests/Feature/Api/) before each frontend release. Fix API bugs before frontend depends on them.

### 12.2 Risk: Frontend Becomes Complex

**Mitigation**: Use component libraries (Headless UI, shadcn/vue) to reduce custom code. Keep business logic in Pinia stores (not components).

### 12.3 Risk: Performance Regression

**Mitigation**: Implement caching layer (Section 8.1) and monitor API response times. Set SLA: API response < 500ms p95.

### 12.4 Risk: Token Expiration Issues

**Mitigation**: Implement auto-refresh endpoint (Section 5.2). Test token lifecycle thoroughly in Phase 1.

### 12.5 Risk: External Consumer Impact

**Mitigation**: Maintain API contract exactly as defined. Run API contract tests before any backend change. Communicate in writing before any API modification.

---

## 13. Post-Migration Roadmap

After SPA migration (Phases 0–4):

### 13.1 Advanced Features (Optional)

1. **Offline Sync**: Sync changes made offline when reconnected (IndexedDB + ServiceWorker)
2. **Real-Time Collaboration**: WebSocket push for multi-user updates
3. **Mobile App**: Native iOS/Android using same API
4. **Analytics Dashboard**: Advanced reporting with chart.js or D3.js
5. **Export/Import**: Bulk operations, data export in CSV/Excel

### 13.2 Infrastructure Upgrades

1. **API Gateway**: Rate limiting, request transformation, versioning
2. **Cache Layer**: Redis for session/data caching
3. **Search Optimization**: Elasticsearch for advanced tester/fixture search
4. **CDN**: Serve SPA assets from CDN for global performance

---

## Appendix: Sample Project Structure (Post-Migration)

```
src/
├── components/              # Reusable Vue components
│   ├── TesterForm.vue
│   ├── TesterTable.vue
│   ├── FixtureModal.vue
│   └── ...
├── pages/                   # Page-level components
│   ├── Login.vue
│   ├── Dashboard.vue
│   ├── Testers/
│   │   ├── Index.vue       # List page
│   │   ├── Show.vue        # Detail page
│   │   └── Edit.vue        # Edit page
│   ├── Fixtures/
│   ├── Maintenance/
│   ├── Calibration/
│   ├── EventLogs/
│   └── Inventory/
├── services/               # API client
│   ├── api.ts             # Main API client
│   └── transformers.ts    # Response transformers
├── stores/                 # State management (Pinia)
│   ├── authStore.ts
│   ├── testerStore.ts
│   └── ...
├── router/                 # Vue Router
│   └── index.ts           # Route definitions
├── types/                  # TypeScript interfaces
│   ├── models.ts          # Model interfaces
│   ├── api.ts             # API response types
│   └── ...
├── utils/                  # Helper functions
│   ├── formatters.ts      # Date/number formatting
│   ├── validators.ts      # Form validation
│   └── ...
├── App.vue                # Root component
├── main.ts                # App entry point
└── style.css              # Global styles (Tailwind)

tests/
├── unit/                   # Unit tests (Vitest)
│   └── services/
│       └── api.test.ts
├── e2e/                    # E2E tests (Playwright/Cypress)
│   ├── login.spec.ts
│   ├── testers.spec.ts
│   └── ...
└── fixtures/              # Test data
    └── users.ts
```

---

## Conclusion

This migration strategy provides a phased, low-risk path to modernize the Tester Register frontend while maintaining full backend API stability. External consumers experience zero disruption. By following this roadmap, the system gains:

✅ **Responsive, modern UI** (Vue/React SPA)  
✅ **Better developer experience** (TypeScript, component reusability)  
✅ **Offline-ready foundation** (with IndexedDB)  
✅ **Mobile-ready API** (already exists, now frontend-integrated)  
✅ **Maintained API backward compatibility** (no breaking changes)

**Estimated Go-Live**: 18 weeks from Phase 0 kickoff.

---

**Document Version**: 1.0 (Future Migration)  
**Last Updated**: 2026-04-30  
**Status**: Proposed  
**Next Step**: Obtain stakeholder approval to begin Phase 0 (Preparation)
