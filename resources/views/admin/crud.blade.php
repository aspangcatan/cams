<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CAMS Admin Modules</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ["Space Grotesk", "ui-sans-serif", "sans-serif"],
                    },
                    colors: {
                        ink: "#0B132B",
                        dawn: "#1C2541",
                        mint: "#3A506B",
                        tide: "#5BC0BE",
                        mist: "#E9F7F6"
                    },
                    boxShadow: {
                        premium: "0 20px 45px rgba(8, 15, 33, 0.15)"
                    }
                }
            }
        };
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-mist via-white to-slate-100 text-ink">
    @php
        $authName = trim(implode(' ', array_filter([
            $authUser->fname ?? null,
            $authUser->mname ?? null,
            $authUser->lname ?? null,
            $authUser->suffix ?? null,
        ])));
        $authName = $authName !== '' ? $authName : ($authUser->username ?? 'User');
        $avatarName = urlencode($authName);
    @endphp

    <div id="globalLoader" class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-900/35 backdrop-blur-sm">
        <div class="rounded-2xl border border-white/70 bg-white/95 px-8 py-6 shadow-premium">
            <div class="mx-auto h-12 w-12 animate-spin rounded-full border-4 border-slate-200 border-t-tide"></div>
            <p id="globalLoaderText" class="mt-4 text-center text-sm font-semibold text-slate-700">Loading...</p>
        </div>
    </div>

    <div class="mx-auto max-w-7xl p-4 md:p-8">
        <header class="relative z-50 mb-6 overflow-visible rounded-3xl border border-white/70 bg-white/70 p-6 shadow-premium backdrop-blur-md">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-mint">CAMS Control Center</p>
            <div class="mt-2 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold md:text-4xl">CRUD Module Workspace</h1>
                    <p class="mt-1 text-sm text-slate-600">Manage Tdh User and Central Access records in one interface.</p>
                </div>
                <div class="relative z-[120] self-start md:self-auto">
                    <button id="profileMenuBtn" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm transition hover:bg-slate-50">
                        <img src="https://ui-avatars.com/api/?name={{ $avatarName }}&background=0B132B&color=ffffff&size=128" alt="Profile" class="h-9 w-9 rounded-full border border-slate-200 object-cover">
                        <div class="text-left">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-mint">Live API Powered</p>
                            <p class="text-sm font-semibold text-ink">{{ $authName }}</p>
                        </div>
                        <svg class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.162l3.71-3.93a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div id="profileMenu" class="absolute right-0 top-full z-[130] mt-2 hidden w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-premium">
                        <button id="openChangePasswordBtn" class="w-full rounded-xl px-3 py-2 text-left text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Change Password</button>
                        <form id="logoutForm" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="mt-1 w-full rounded-xl px-3 py-2 text-left text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Sign Out</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <div class="grid gap-6 lg:grid-cols-[280px_1fr]">
            <aside class="rounded-3xl border border-white/70 bg-white/75 p-4 shadow-premium backdrop-blur-md">
                <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Modules</p>
                <nav id="moduleNav" class="space-y-2"></nav>
            </aside>

            <main class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-premium backdrop-blur-md md:p-6">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 id="moduleTitle" class="text-xl font-bold md:text-2xl"></h2>
                        <p id="moduleDescription" class="text-sm text-slate-500"></p>
                    </div>
                    <button id="createBtn" class="inline-flex items-center justify-center rounded-xl bg-ink px-4 py-2 text-sm font-semibold text-white transition hover:bg-dawn">
                        Add Record
                    </button>
                </div>

                <div class="mb-4">
                    <input id="searchInput" type="text" placeholder="Search current module..." class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none ring-tide transition focus:ring-2">
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr id="tableHead"></tr>
                            </thead>
                            <tbody id="tableBody" class="divide-y divide-slate-100 bg-white"></tbody>
                        </table>
                    </div>
                </div>
                <p id="statusText" class="mt-3 text-sm text-slate-500"></p>
                <div id="paginationControls" class="mt-4 hidden items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <button id="prevPageBtn" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50">Previous</button>
                    <p id="pageInfo" class="text-sm font-medium text-slate-600"></p>
                    <button id="nextPageBtn" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50">Next</button>
                </div>
            </main>
        </div>
    </div>

    <div id="formModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/45 p-4">
        <div class="w-full max-w-2xl rounded-3xl border border-white/70 bg-white p-6 shadow-premium">
            <div class="mb-4 flex items-center justify-between">
                <h3 id="modalTitle" class="text-xl font-bold"></h3>
                <button id="closeModalBtn" class="rounded-lg border border-slate-300 px-3 py-1 text-sm text-slate-600 hover:bg-slate-50">Close</button>
            </div>
            <form id="recordForm" class="grid gap-4 md:grid-cols-2"></form>
            <p id="formError" class="mt-3 hidden rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700"></p>
            <div class="mt-5 flex justify-end gap-3">
                <button id="cancelBtn" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancel</button>
                <button id="saveBtn" class="rounded-xl bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-dawn">Save</button>
            </div>
        </div>
    </div>

    <div id="assignModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/45 p-4">
        <div class="w-full max-w-5xl rounded-3xl border border-white/70 bg-white p-6 shadow-premium">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 id="assignModalTitle" class="text-xl font-bold">Assign Access</h3>
                    <p id="assignModalSubtitle" class="text-sm text-slate-500"></p>
                </div>
                <button id="closeAssignModalBtn" class="rounded-lg border border-slate-300 px-3 py-1 text-sm text-slate-600 hover:bg-slate-50">Close</button>
            </div>

            <div class="grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-[1fr_1fr_auto]">
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-600">Users</label>
                    <select id="assignmentUsersSelect" multiple class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"></select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-600">Role / Level</label>
                    <select id="assignmentRoleSelect" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"></select>
                </div>
                <div class="flex items-end">
                    <button id="saveAssignmentsBtn" class="h-10 rounded-xl bg-ink px-4 text-sm font-semibold text-white hover:bg-dawn">Assign</button>
                </div>
            </div>

            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                <div class="border-b border-slate-200 bg-slate-50 p-3">
                    <input id="assignmentSearchInput" type="text" placeholder="Search assigned users by name..." class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none ring-tide transition focus:ring-2">
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">User</th>
                                <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Level</th>
                                <th class="px-3 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="assignmentsTableBody" class="divide-y divide-slate-100 bg-white"></tbody>
                    </table>
                </div>
            </div>
            <div id="assignmentPaginationControls" class="mt-3 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-3">
                <button id="assignmentPrevBtn" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50">Previous</button>
                <p id="assignmentPageInfo" class="text-sm font-medium text-slate-600"></p>
                <button id="assignmentNextBtn" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50">Next</button>
            </div>
        </div>
    </div>

    <div id="passwordModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-900/45 p-4">
        <div class="w-full max-w-xl rounded-3xl border border-white/70 bg-white p-6 shadow-premium">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xl font-bold">Change Password</h3>
                <button id="closePasswordModalBtn" class="rounded-lg border border-slate-300 px-3 py-1 text-sm text-slate-600 hover:bg-slate-50">Close</button>
            </div>
            <form id="passwordForm" class="space-y-4">
                <label class="block">
                    <span class="mb-1 block text-sm font-semibold text-slate-600">Current Password</span>
                    <input type="password" name="current_password" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none ring-tide transition focus:ring-2">
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-semibold text-slate-600">New Password</span>
                    <input type="password" name="new_password" required minlength="4" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none ring-tide transition focus:ring-2">
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-semibold text-slate-600">Confirm New Password</span>
                    <input type="password" name="new_password_confirmation" required minlength="4" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none ring-tide transition focus:ring-2">
                </label>
            </form>
            <div class="mt-5 flex justify-end gap-3">
                <button id="cancelPasswordBtn" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancel</button>
                <button id="savePasswordBtn" class="rounded-xl bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-dawn">Update Password</button>
            </div>
        </div>
    </div>

    <script>
        const WEB_BASE = @json(url('/'));
        const API_BASE = @json(url('/api'));
        const joinUrl = (base, path) => `${String(base).replace(/\/+$/, "")}/${String(path).replace(/^\/+/, "")}`;

        const modules = [
            {
                key: "designations",
                title: "Designations",
                description: "tdh_user.designation",
                endpoint: joinUrl(API_BASE, "tdh-user/designations"),
                fields: [
                    { name: "description", label: "Description", type: "text", required: true }
                ]
            },
            {
                key: "divisions",
                title: "Divisions",
                description: "tdh_user.division",
                endpoint: joinUrl(API_BASE, "tdh-user/divisions"),
                fields: [
                    { name: "description", label: "Description", type: "text", required: true },
                    { name: "head", label: "Head User", type: "select", source: "users", searchable: true },
                    { name: "code", label: "Code", type: "text" }
                ]
            },
            {
                key: "sections",
                title: "Sections",
                description: "tdh_user.section",
                endpoint: joinUrl(API_BASE, "tdh-user/sections"),
                fields: [
                    { name: "description", label: "Description", type: "text", required: true },
                    { name: "head", label: "Head User", type: "select", source: "users", searchable: true },
                    { name: "code", label: "Code", type: "text" },
                    { name: "division", label: "Division", type: "select", source: "divisions", searchable: true },
                    { name: "subsection", label: "Parent Section", type: "select", source: "sections", searchable: true }
                ]
            },
            {
                key: "users",
                title: "Users",
                description: "tdh_user.users",
                endpoint: joinUrl(API_BASE, "tdh-user/users"),
                fields: [
                    { name: "fname", label: "First Name", type: "text", required: true },
                    { name: "mname", label: "Middle Name", type: "text", persistEmpty: true },
                    { name: "lname", label: "Last Name", type: "text", required: true },
                    { name: "birthdate", label: "Birthdate", type: "date" },
                    { name: "date_hired", label: "Date Hired", type: "date" },
                    { name: "sex", label: "Sex", type: "select", source: "sexes", valueType: "string", searchable: true },
                    { name: "employee_type", label: "Employment Type", type: "select", source: "employeeTypes", valueType: "string", searchable: true },
                    { name: "is_deployed", label: "Deployed Bukas", type: "select", source: "deploymentFlags", searchable: true },
                    { name: "employee_no", label: "Agency Employee No.", type: "text" },
                    { name: "suffix", label: "Suffix", type: "text" },
                    { name: "username", label: "Username", type: "text", required: true },
                    { name: "password", label: "Password", type: "password", requiredOnCreate: true, hideInTable: true },
                    { name: "designation", label: "Designation", type: "select", source: "designations", searchable: true },
                    { name: "division", label: "Division", type: "select", source: "divisions", searchable: true },
                    { name: "section", label: "Section", type: "select", source: "sections", searchable: true }
                ]
            },
            {
                key: "systems",
                title: "Systems",
                description: "central_access.systems",
                endpoint: joinUrl(API_BASE, "central-access/systems"),
                fields: [
                    { name: "system", label: "System", type: "text", required: true },
                    { name: "description", label: "Description", type: "text" }
                ]
            },
            {
                key: "access-rights",
                title: "Access Rights",
                description: "central_access.systems_access_rights",
                endpoint: joinUrl(API_BASE, "central-access/access-rights"),
                fields: [
                    { name: "system_id", label: "System", type: "select", source: "systems", searchable: true, required: true },
                    { name: "role", label: "Role", type: "text", required: true },
                    { name: "role_description", label: "Role Description", type: "text" }
                ]
            },
            {
                key: "device-lists",
                title: "Device Lists",
                description: "central_access.device_lists",
                endpoint: joinUrl(API_BASE, "central-access/device-lists"),
                fields: [
                    { name: "userid", label: "User ID", type: "number", required: true },
                    { name: "android_id", label: "Android ID", type: "text", required: true }
                ]
            }
        ];

        function getOrderedModules() {
            return [...modules].sort((a, b) => {
                if (a.key === "users") return -1;
                if (b.key === "users") return 1;
                return 0;
            });
        }

        const state = {
            activeModule: modules.find((module) => module.key === "users") || modules[0],
            records: [],
            filteredRecords: [],
            editingRecord: null,
            options: {
                users: [],
                designations: [],
                divisions: [],
                sections: [],
                systems: [],
                sexes: [
                    { id: "MALE", label: "MALE" },
                    { id: "FEMALE", label: "FEMALE" }
                ],
                employeeTypes: [
                    { id: "Job Order", label: "Job Order" },
                    { id: "Permanent", label: "Permanent" },
                    { id: "Resigned", label: "Resigned" },
                    { id: "Retired", label: "Retired" },
                    { id: "Temporary", label: "Temporary" },
                    { id: "EOT", label: "EOT" },
                    { id: "COS", label: "COS" }
                ],
                deploymentFlags: [
                    { id: 1, label: "Yes" },
                    { id: 2, label: "No" }
                ]
            },
            pagination: {
                currentPage: 1,
                lastPage: 1,
                perPage: 10,
                total: 0
            },
            searchTimer: null,
            selectInstances: [],
            assignment: {
                system: null,
                roles: [],
                rows: [],
                roleSelect: null,
                usersSelect: null,
                pagination: {
                    currentPage: 1,
                    lastPage: 1,
                    perPage: 10,
                    total: 0
                },
                search: "",
                searchTimer: null
            }
        };

        const moduleNav = document.getElementById("moduleNav");
        const profileMenuBtn = document.getElementById("profileMenuBtn");
        const profileMenu = document.getElementById("profileMenu");
        const openChangePasswordBtn = document.getElementById("openChangePasswordBtn");
        const moduleTitle = document.getElementById("moduleTitle");
        const moduleDescription = document.getElementById("moduleDescription");
        const createBtn = document.getElementById("createBtn");
        const tableHead = document.getElementById("tableHead");
        const tableBody = document.getElementById("tableBody");
        const searchInput = document.getElementById("searchInput");
        const statusText = document.getElementById("statusText");
        const formModal = document.getElementById("formModal");
        const modalTitle = document.getElementById("modalTitle");
        const recordForm = document.getElementById("recordForm");
        const formError = document.getElementById("formError");
        const saveBtn = document.getElementById("saveBtn");
        const closeModalBtn = document.getElementById("closeModalBtn");
        const cancelBtn = document.getElementById("cancelBtn");
        const assignModal = document.getElementById("assignModal");
        const assignModalTitle = document.getElementById("assignModalTitle");
        const assignModalSubtitle = document.getElementById("assignModalSubtitle");
        const closeAssignModalBtn = document.getElementById("closeAssignModalBtn");
        const assignmentUsersSelect = document.getElementById("assignmentUsersSelect");
        const assignmentRoleSelect = document.getElementById("assignmentRoleSelect");
        const assignmentSearchInput = document.getElementById("assignmentSearchInput");
        const saveAssignmentsBtn = document.getElementById("saveAssignmentsBtn");
        const assignmentsTableBody = document.getElementById("assignmentsTableBody");
        const passwordModal = document.getElementById("passwordModal");
        const passwordForm = document.getElementById("passwordForm");
        const closePasswordModalBtn = document.getElementById("closePasswordModalBtn");
        const cancelPasswordBtn = document.getElementById("cancelPasswordBtn");
        const savePasswordBtn = document.getElementById("savePasswordBtn");
        const assignmentPaginationControls = document.getElementById("assignmentPaginationControls");
        const assignmentPrevBtn = document.getElementById("assignmentPrevBtn");
        const assignmentNextBtn = document.getElementById("assignmentNextBtn");
        const assignmentPageInfo = document.getElementById("assignmentPageInfo");
        const paginationControls = document.getElementById("paginationControls");
        const prevPageBtn = document.getElementById("prevPageBtn");
        const nextPageBtn = document.getElementById("nextPageBtn");
        const pageInfo = document.getElementById("pageInfo");

        function sortOptionsByLabel(options) {
            return [...(options || [])].sort((a, b) => String(a?.label || "").localeCompare(String(b?.label || ""), undefined, { sensitivity: "base" }));
        }

        function renderModuleNav() {
            moduleNav.innerHTML = getOrderedModules().map((mod) => {
                const active = mod.key === state.activeModule.key;
                return `
                    <button
                        data-key="${mod.key}"
                        class="w-full rounded-xl px-3 py-2 text-left text-sm font-semibold transition ${
                            active
                                ? "bg-ink text-white shadow-md"
                                : "bg-white text-slate-700 hover:bg-slate-100"
                        }"
                    >
                        ${mod.title}
                    </button>
                `;
            }).join("");

            moduleNav.querySelectorAll("button").forEach((btn) => {
                btn.addEventListener("click", () => {
                    const found = modules.find((mod) => mod.key === btn.dataset.key);
                    if (!found) return;
                    state.activeModule = found;
                    state.editingRecord = null;
                    state.pagination.currentPage = 1;
                    state.pagination.lastPage = 1;
                    state.pagination.total = 0;
                    searchInput.value = "";
                    renderModuleNav();
                    refreshModule();
                });
            });
        }

        function renderTable() {
            if (state.activeModule.key === "users") {
                const userColumns = ["avatar", "name", "username", "designation", "section"];
                tableHead.innerHTML = userColumns.map((col) => `
                    <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">${col === "avatar" ? "" : col}</th>
                `).join("") + `<th class="px-3 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Actions</th>`;

                if (!state.filteredRecords.length) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="${userColumns.length + 1}" class="px-3 py-8 text-center text-sm text-slate-500">
                                No records found.
                            </td>
                        </tr>
                    `;
                    return;
                }

                tableBody.innerHTML = state.filteredRecords.map((row) => `
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-3">
                            <div class="h-11 w-11 overflow-hidden rounded-2xl border border-white/70 bg-gradient-to-br from-slate-100 to-slate-200 shadow-md ring-2 ring-white">
                                <img
                                    src="${escapeAttr(getUserPictureUrl(row))}"
                                    alt="${escapeAttr(buildFullName(row))}"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(buildFullName(row))}&background=0B132B&color=ffffff&size=128';"
                                    class="h-full w-full object-cover"
                                >
                            </div>
                        </td>
                        <td class="px-3 py-3 text-sm text-slate-700">${escapeHtml(buildFullName(row))}</td>
                        <td class="px-3 py-3 text-sm text-slate-700">${escapeHtml(formatValue(row.username))}</td>
                        <td class="px-3 py-3 text-sm text-slate-700">${escapeHtml(getOptionLabel("designations", row.designation))}</td>
                        <td class="px-3 py-3 text-sm text-slate-700">${escapeHtml(getOptionLabel("sections", row.section))}</td>
                        <td class="px-3 py-3 text-right">
                            <div class="inline-flex gap-2">
                                <button data-id="${row.id}" data-action="edit" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</button>
                                <button data-id="${row.id}" data-action="reset" class="rounded-lg border border-amber-300 px-3 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-50">Reset</button>
                                <button data-id="${row.id}" data-action="delete" class="rounded-lg border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">Delete</button>
                            </div>
                        </td>
                    </tr>
                `).join("");

                tableBody.querySelectorAll("button").forEach((btn) => {
                    const id = Number(btn.dataset.id);
                    const action = btn.dataset.action;
                    if (action === "edit") {
                        btn.addEventListener("click", () => openEdit(id));
                    }
                    if (action === "delete") {
                        btn.addEventListener("click", () => destroyRecord(id));
                    }
                    if (action === "reset") {
                        btn.addEventListener("click", () => resetPassword(id));
                    }
                });

                return;
            }

            const columns = ["id", ...state.activeModule.fields.filter((f) => !f.hideInTable).map((f) => f.name)];
            tableHead.innerHTML = columns.map((col) => `
                <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">${col}</th>
            `).join("") + `<th class="px-3 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Actions</th>`;

            if (!state.filteredRecords.length) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="${columns.length + 1}" class="px-3 py-8 text-center text-sm text-slate-500">
                            No records found.
                        </td>
                    </tr>
                `;
                return;
            }

            tableBody.innerHTML = state.filteredRecords.map((row) => `
                <tr class="hover:bg-slate-50">
                    ${columns.map((col) => `<td class="px-3 py-3 text-sm text-slate-700">${escapeHtml(formatModuleCellValue(row, col))}</td>`).join("")}
                    <td class="px-3 py-3 text-right">
                        <div class="inline-flex gap-2">
                            ${state.activeModule.key === "device-lists"
                                ? `<button data-id="${row.id}" data-action="approve" class="rounded-lg border border-emerald-300 px-3 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Approve</button>`
                                : `<button data-id="${row.id}" data-action="edit" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</button>`
                            }
                            ${state.activeModule.key === "systems"
                                ? `<button data-id="${row.id}" data-action="assign" class="rounded-lg border border-indigo-300 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">Assign</button>`
                                : ""}
                            ${state.activeModule.key === "users"
                                ? `<button data-id="${row.id}" data-action="reset" class="rounded-lg border border-amber-300 px-3 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-50">Reset</button>`
                                : ""}
                            <button data-id="${row.id}" data-action="delete" class="rounded-lg border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">Delete</button>
                        </div>
                    </td>
                </tr>
            `).join("");

            tableBody.querySelectorAll("button").forEach((btn) => {
                const id = Number(btn.dataset.id);
                const action = btn.dataset.action;
                if (action === "edit") {
                    btn.addEventListener("click", () => openEdit(id));
                }
                if (action === "delete") {
                    btn.addEventListener("click", () => destroyRecord(id));
                }
                if (action === "reset") {
                    btn.addEventListener("click", () => resetPassword(id));
                }
                if (action === "approve") {
                    btn.addEventListener("click", () => approveDevice(id));
                }
                if (action === "assign") {
                    btn.addEventListener("click", () => openAssignAccess(id));
                }
            });
        }

        function renderForm() {
            const activeFields = state.activeModule.fields;
            recordForm.innerHTML = activeFields.map((field) => {
                const value = state.editingRecord ? state.editingRecord[field.name] ?? "" : "";
                const isRequired = Boolean(field.required || (field.requiredOnCreate && !state.editingRecord));
                let fieldOptions = sortOptionsByLabel(state.options[field.source] || []);

                if (state.activeModule.key === "users" && field.name === "section") {
                    const selectedDivision = state.editingRecord
                        ? String(state.editingRecord.division ?? "")
                        : "";
                    fieldOptions = filterSectionsByDivision(selectedDivision);
                }

                if (field.type === "select") {
                    const options = fieldOptions.map((option) => `
                        <option value="${escapeAttr(option.id)}" ${String(value) === String(option.id) ? "selected" : ""}>
                            ${escapeHtml(option.label)}
                        </option>
                    `).join("");

                    return `
                        <label class="block">
                            <span class="mb-1 block text-sm font-semibold text-slate-600">${field.label}${isRequired ? " *" : ""}</span>
                            <select
                                name="${field.name}"
                                ${isRequired ? "required" : ""}
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none ring-tide transition focus:ring-2 js-searchable-select"
                            >
                                <option value="">Select ${escapeHtml(field.label.toLowerCase())}</option>
                                ${options}
                            </select>
                        </label>
                    `;
                }

                return `
                    <label class="block">
                        <span class="mb-1 block text-sm font-semibold text-slate-600">${field.label}${isRequired ? " *" : ""}</span>
                        <input
                            name="${field.name}"
                            type="${field.type}"
                            value="${escapeAttr(value)}"
                            ${isRequired ? "required" : ""}
                            ${field.type === "password" ? "autocomplete=\"new-password\"" : ""}
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none ring-tide transition focus:ring-2"
                        />
                    </label>
                `;
            }).join("");

            initializeSearchableSelects();
            bindUserDivisionSectionFilter();
        }

        async function loadUsersOptions() {
            if (state.options.users.length > 0) return;

            const response = await fetch(`${joinUrl(API_BASE, "tdh-user/users")}?all=1`);
            if (!response.ok) throw new Error("Unable to load users for Head dropdown.");
            const users = await response.json();

            state.options.users = users.map((user) => {
                const fullName = [user.fname, user.mname, user.lname, user.suffix]
                    .filter((part) => part && String(part).trim() !== "")
                    .join(" ");
                const fallback = user.username ? `@${user.username}` : `User #${user.id}`;

                return {
                    id: user.id,
                    label: fullName || fallback
                };
            }).sort((a, b) => String(a.label).localeCompare(String(b.label), undefined, { sensitivity: "base" }));
        }

        async function loadSimpleOptions(source, endpoint) {
            if ((state.options[source] || []).length > 0) return;

            const withAll = endpoint.includes("?") ? `${endpoint}&all=1` : `${endpoint}?all=1`;
            const response = await fetch(withAll);
            if (!response.ok) throw new Error(`Unable to load ${source} options.`);
            const rows = await response.json();

            state.options[source] = rows.map((row) => {
                const label = source === "systems"
                    ? (row.description || row.system || `ID ${row.id}`)
                    : (row.description || row.name || row.system || `ID ${row.id}`);
                return {
                    id: row.id,
                    label,
                    division: row.division ?? null,
                    division_id: row.division_id ?? null
                };
            }).sort((a, b) => String(a.label).localeCompare(String(b.label), undefined, { sensitivity: "base" }));
        }

        function filterSectionsByDivision(divisionId) {
            const selectedDivision = String(divisionId || "").trim();
            if (selectedDivision === "") return [];

            return (state.options.sections || []).filter((section) => {
                const sectionDivision = section.division ?? section.division_id ?? null;
                return String(sectionDivision ?? "") === selectedDivision;
            });
        }

        function bindUserDivisionSectionFilter() {
            if (state.activeModule.key !== "users") return;

            const divisionSelect = recordForm.querySelector('select[name="division"]');
            const sectionSelect = recordForm.querySelector('select[name="section"]');
            if (!divisionSelect || !sectionSelect) return;

            const updateSectionOptions = () => {
                const filteredSections = filterSectionsByDivision(divisionSelect.value);
                const currentSection = String(sectionSelect.value || "");
                const hasCurrentSection = filteredSections.some((section) => String(section.id) === currentSection);
                const nextSectionValue = hasCurrentSection ? currentSection : "";

                sectionSelect.innerHTML = `<option value="">Select section</option>${filteredSections.map((section) => `
                    <option value="${escapeAttr(section.id)}" ${String(section.id) === nextSectionValue ? "selected" : ""}>
                        ${escapeHtml(section.label)}
                    </option>
                `).join("")}`;
                sectionSelect.value = nextSectionValue;

                if (sectionSelect.tomselect) {
                    sectionSelect.tomselect.clearOptions();
                    sectionSelect.tomselect.addOptions(filteredSections.map((section) => ({
                        value: String(section.id),
                        text: section.label
                    })));
                    sectionSelect.tomselect.refreshOptions(false);
                    sectionSelect.tomselect.setValue(nextSectionValue, true);
                }
            };

            divisionSelect.addEventListener("change", updateSectionOptions);
            if (divisionSelect.tomselect) {
                divisionSelect.tomselect.on("change", updateSectionOptions);
            }
            updateSectionOptions();
        }

        async function ensureFieldOptions() {
            const needsUsers = state.activeModule.fields.some((field) => field.source === "users");
            const needsDesignations = state.activeModule.fields.some((field) => field.source === "designations");
            const needsDivisions = state.activeModule.fields.some((field) => field.source === "divisions");
            const needsSections = state.activeModule.fields.some((field) => field.source === "sections");
            const needsSystems = state.activeModule.fields.some((field) => field.source === "systems");

            if (needsUsers) {
                await loadUsersOptions();
            }
            if (needsDesignations) {
                await loadSimpleOptions("designations", joinUrl(API_BASE, "tdh-user/designations"));
            }
            if (needsDivisions) {
                await loadSimpleOptions("divisions", joinUrl(API_BASE, "tdh-user/divisions"));
            }
            if (needsSections) {
                await loadSimpleOptions("sections", joinUrl(API_BASE, "tdh-user/sections"));
            }
            if (needsSystems) {
                await loadSimpleOptions("systems", joinUrl(API_BASE, "central-access/systems"));
            }
        }

        async function preloadAllOptions() {
            await Promise.all([
                loadUsersOptions(),
                loadSimpleOptions("designations", joinUrl(API_BASE, "tdh-user/designations")),
                loadSimpleOptions("divisions", joinUrl(API_BASE, "tdh-user/divisions")),
                loadSimpleOptions("sections", joinUrl(API_BASE, "tdh-user/sections")),
                loadSimpleOptions("systems", joinUrl(API_BASE, "central-access/systems"))
            ]);
        }

        function initializeSearchableSelects() {
            state.selectInstances.forEach((instance) => instance.destroy());
            state.selectInstances = [];

            if (!window.TomSelect) return;

            recordForm.querySelectorAll(".js-searchable-select").forEach((element) => {
                const instance = new TomSelect(element, {
                    create: false,
                    allowEmptyOption: true,
                    maxOptions: 5000,
                    hidePlaceholder: false,
                    searchField: ["text"],
                    placeholder: "Type to search..."
                });
                state.selectInstances.push(instance);
            });
        }

        function applySearch() {
            if (state.searchTimer) {
                clearTimeout(state.searchTimer);
            }
            state.searchTimer = setTimeout(() => {
                state.pagination.currentPage = 1;
                refreshModule(1);
            }, 300);
        }

        function renderPaginationControls() {
            paginationControls.classList.remove("hidden");
            paginationControls.classList.add("flex");
            pageInfo.textContent = `Page ${state.pagination.currentPage} of ${state.pagination.lastPage} (${state.pagination.total} record(s))`;
            prevPageBtn.disabled = state.pagination.currentPage <= 1;
            nextPageBtn.disabled = state.pagination.currentPage >= state.pagination.lastPage;
        }

        async function refreshModule(page = null) {
            moduleTitle.textContent = state.activeModule.title;
            moduleDescription.textContent = state.activeModule.description;
            searchInput.placeholder = state.activeModule.key === "users"
                ? "Search users by name..."
                : "Search current module...";
            statusText.textContent = "Loading records...";
            tableBody.innerHTML = `
                <tr>
                    <td colspan="99" class="px-3 py-10 text-center">
                        <div class="inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600">
                            <span class="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-tide"></span>
                            Fetching records...
                        </div>
                    </td>
                </tr>
            `;
            renderPaginationControls();

            try {
                let endpoint = state.activeModule.endpoint;
                const selectedPage = page ?? state.pagination.currentPage ?? 1;
                const params = new URLSearchParams({
                    page: String(selectedPage),
                    per_page: String(state.pagination.perPage)
                });

                const searchTerm = searchInput.value.trim();
                if (searchTerm !== "") {
                    params.set("search", searchTerm);
                }
                endpoint = `${endpoint}?${params.toString()}`;

                const response = await fetch(endpoint);
                if (!response.ok) throw new Error("Unable to load records");

                const result = await response.json();
                if (Array.isArray(result)) {
                    state.records = result;
                    state.pagination.currentPage = 1;
                    state.pagination.lastPage = 1;
                    state.pagination.total = result.length;
                } else {
                    state.records = result.data || [];
                    state.pagination.currentPage = result.current_page || 1;
                    state.pagination.lastPage = result.last_page || 1;
                    state.pagination.total = result.total || 0;
                }

                state.filteredRecords = [...state.records];
                renderTable();
                renderPaginationControls();
                statusText.textContent = `${state.records.length} record(s) loaded on this page.`;
            } catch (error) {
                statusText.textContent = "Failed to load records.";
                renderPaginationControls();
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="99" class="px-3 py-8 text-center text-sm text-rose-600">Error loading data.</td>
                    </tr>
                `;
            }
        }

        async function openCreate() {
            state.editingRecord = null;
            await ensureFieldOptions();
            modalTitle.textContent = `Add ${state.activeModule.title.slice(0, -1) || state.activeModule.title}`;
            formError.classList.add("hidden");
            formError.textContent = "";
            renderForm();
            formModal.classList.remove("hidden");
            formModal.classList.add("flex");
        }

        async function openEdit(id) {
            const found = state.records.find((row) => Number(row.id) === Number(id));
            if (!found) return;
            state.editingRecord = found;
            await ensureFieldOptions();
            modalTitle.textContent = `Edit ${state.activeModule.title.slice(0, -1) || state.activeModule.title} #${id}`;
            formError.classList.add("hidden");
            formError.textContent = "";
            renderForm();
            formModal.classList.remove("hidden");
            formModal.classList.add("flex");
        }

        function closeModal() {
            formModal.classList.add("hidden");
            formModal.classList.remove("flex");
        }

        async function saveRecord() {
            formError.classList.add("hidden");
            formError.textContent = "";

            const formData = new FormData(recordForm);
            const payload = {};
            state.activeModule.fields.forEach((field) => {
                const value = formData.get(field.name);
                if (value === null) return;

                if (value === "") {
                    if (field.persistEmpty) {
                        payload[field.name] = "";
                    }
                    return;
                }

                if (field.type === "number" || (field.type === "select" && field.valueType !== "string")) {
                    payload[field.name] = Number(value);
                    return;
                }

                payload[field.name] = String(value);
            });

            const isEdit = Boolean(state.editingRecord);
            const url = isEdit ? `${state.activeModule.endpoint}/${state.editingRecord.id}` : state.activeModule.endpoint;
            const method = isEdit ? "PUT" : "POST";

            saveBtn.disabled = true;
            saveBtn.textContent = "Saving...";

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const data = await response.json().catch(() => ({}));
                    const msg = data.message || "Failed to save record.";
                    const details = data.errors ? " " + Object.values(data.errors).flat().join(" ") : "";
                    throw new Error(msg + details);
                }

                closeModal();
                await refreshModule();
                showSuccessToast("Record saved successfully.");
            } catch (error) {
                formError.textContent = error.message;
                formError.classList.remove("hidden");
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = "Save";
            }
        }

        async function destroyRecord(id) {
            const confirmed = await confirmAction("Delete this record?", `Record #${id} will be permanently removed.`, "Yes, delete it");
            if (!confirmed) {
                return;
            }

            try {
                const response = await fetch(`${state.activeModule.endpoint}/${id}`, {
                    method: "DELETE",
                    headers: {
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                if (!response.ok) throw new Error("Delete failed");
                await refreshModule(state.pagination.currentPage);
                showSuccessToast("Record deleted.");
            } catch (error) {
                showErrorAlert("Unable to delete record.");
            }
        }

        async function resetPassword(id) {
            const confirmed = await confirmAction("Reset password?", `Set user #${id} password to 123456.`, "Yes, reset");
            if (!confirmed) {
                return;
            }

            try {
                const response = await fetch(joinUrl(API_BASE, `tdh-user/users/${id}/reset-password`), {
                    method: "POST",
                    headers: {
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                if (!response.ok) throw new Error("Reset failed");
                await refreshModule(state.pagination.currentPage);
                showSuccessToast("Password reset successful.");
            } catch (error) {
                showErrorAlert("Unable to reset password.");
            }
        }

        async function approveDevice(id) {
            const confirmed = await confirmAction("Approve device request?", `This will update the user's android_id and remove request #${id}.`, "Yes, approve");
            if (!confirmed) {
                return;
            }

            try {
                const response = await fetch(joinUrl(API_BASE, `central-access/device-lists/${id}/approve`), {
                    method: "POST",
                    headers: {
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                if (!response.ok) throw new Error("Approve failed");
                await refreshModule(state.pagination.currentPage);
                showSuccessToast("Device approved successfully.");
            } catch (error) {
                showErrorAlert("Unable to approve device request.");
            }
        }

        async function openAssignAccess(systemId, page = 1) {
            await loadUsersOptions();
            state.assignment.pagination.currentPage = page;
            assignmentSearchInput.value = state.assignment.search;

            const row = state.records.find((item) => Number(item.id) === Number(systemId));
            state.assignment.system = row || { id: systemId, system: `#${systemId}`, description: "" };
            assignModalTitle.textContent = "Assign Access";
            assignModalSubtitle.textContent = `${state.assignment.system.system || ""} - ${state.assignment.system.description || ""}`.trim();
            assignmentsTableBody.innerHTML = `
                <tr>
                    <td colspan="3" class="px-3 py-8 text-center text-sm text-slate-500">Loading assignments...</td>
                </tr>
            `;

            assignModal.classList.remove("hidden");
            assignModal.classList.add("flex");

            try {
                const params = new URLSearchParams({
                    page: String(page),
                    per_page: String(state.assignment.pagination.perPage)
                });
                const searchTerm = state.assignment.search.trim();
                if (searchTerm !== "") {
                    params.set("search", searchTerm);
                }
                const response = await fetch(`${joinUrl(API_BASE, `central-access/systems/${systemId}/assignments`)}?${params.toString()}`);
                if (!response.ok) throw new Error("Unable to load assignments");
                const payload = await response.json();

                state.assignment.system = payload.system || state.assignment.system;
                state.assignment.roles = payload.roles || [];
                state.assignment.rows = payload.assignments?.data || [];
                state.assignment.pagination.currentPage = payload.assignments?.current_page || 1;
                state.assignment.pagination.lastPage = payload.assignments?.last_page || 1;
                state.assignment.pagination.total = payload.assignments?.total || 0;
                assignModalSubtitle.textContent = `${state.assignment.system.system || ""} - ${state.assignment.system.description || ""}`.trim();

                renderAssignmentFormOptions();
                renderAssignmentsTable();
                renderAssignmentPagination();
                initializeAssignmentSelects();
            } catch (error) {
                assignmentsTableBody.innerHTML = `
                    <tr>
                        <td colspan="3" class="px-3 py-8 text-center text-sm text-rose-600">Failed to load assignments.</td>
                    </tr>
                `;
                renderAssignmentPagination();
            }
        }

        function closeAssignAccess() {
            assignModal.classList.add("hidden");
            assignModal.classList.remove("flex");
            destroyAssignmentSelects();
            state.assignment.rows = [];
            state.assignment.pagination.currentPage = 1;
            state.assignment.pagination.lastPage = 1;
            state.assignment.pagination.total = 0;
            state.assignment.search = "";
            assignmentSearchInput.value = "";
        }

        function openPasswordModal() {
            passwordForm.reset();
            passwordModal.classList.remove("hidden");
            passwordModal.classList.add("flex");
        }

        function closePasswordModal() {
            passwordModal.classList.add("hidden");
            passwordModal.classList.remove("flex");
        }

        async function submitPasswordChange() {
            const formData = new FormData(passwordForm);
            const payload = {
                current_password: String(formData.get("current_password") || ""),
                new_password: String(formData.get("new_password") || ""),
                new_password_confirmation: String(formData.get("new_password_confirmation") || "")
            };

            savePasswordBtn.disabled = true;
            savePasswordBtn.textContent = "Updating...";

            try {
                const response = await fetch(joinUrl(WEB_BASE, "change-password"), {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const data = await response.json().catch(() => ({}));
                    const msg = data.message || "Unable to change password.";
                    throw new Error(msg);
                }

                closePasswordModal();
                showSuccessToast("Password updated successfully.");
            } catch (error) {
                showErrorAlert(error.message || "Unable to change password.");
            } finally {
                savePasswordBtn.disabled = false;
                savePasswordBtn.textContent = "Update Password";
            }
        }

        function renderAssignmentFormOptions() {
            const roleOptions = [...state.assignment.roles].sort((a, b) => {
                const aLabel = a.role_description ? `${a.role} (${a.role_description})` : String(a.role || "");
                const bLabel = b.role_description ? `${b.role} (${b.role_description})` : String(b.role || "");
                return aLabel.localeCompare(bLabel, undefined, { sensitivity: "base" });
            }).map((role) => {
                const label = role.role_description
                    ? `${role.role} (${role.role_description})`
                    : role.role;
                return `<option value="${escapeAttr(role.role)}">${escapeHtml(label)}</option>`;
            }).join("");

            assignmentRoleSelect.innerHTML = `<option value="">Select role</option>${roleOptions}`;

            const userOptions = sortOptionsByLabel(state.options.users || []).map((user) => `
                <option value="${escapeAttr(user.id)}">${escapeHtml(user.label)}</option>
            `).join("");
            assignmentUsersSelect.innerHTML = userOptions;
        }

        function renderAssignmentsTable() {
            if (!state.assignment.rows.length) {
                assignmentsTableBody.innerHTML = `
                    <tr>
                        <td colspan="3" class="px-3 py-8 text-center text-sm text-slate-500">No assigned users yet.</td>
                    </tr>
                `;
                return;
            }

            assignmentsTableBody.innerHTML = state.assignment.rows.map((item) => `
                <tr class="hover:bg-slate-50">
                    <td class="px-3 py-3 text-sm text-slate-700">${escapeHtml(item.user_name || `User #${item.user_id}`)}</td>
                    <td class="px-3 py-3 text-sm text-slate-700">${escapeHtml(item.level || "-")}</td>
                    <td class="px-3 py-3 text-right">
                        <div class="inline-flex gap-2">
                            <button data-assignment-id="${item.id}" data-action="edit-assignment" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</button>
                            <button data-assignment-id="${item.id}" data-action="delete-assignment" class="rounded-lg border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">Delete</button>
                        </div>
                    </td>
                </tr>
            `).join("");

            assignmentsTableBody.querySelectorAll("button").forEach((btn) => {
                const assignmentId = Number(btn.dataset.assignmentId);
                const action = btn.dataset.action;

                if (action === "edit-assignment") {
                    btn.addEventListener("click", () => editAssignment(assignmentId));
                }
                if (action === "delete-assignment") {
                    btn.addEventListener("click", () => deleteAssignment(assignmentId));
                }
            });
        }

        function renderAssignmentPagination() {
            assignmentPaginationControls.classList.remove("hidden");
            assignmentPageInfo.textContent = `Page ${state.assignment.pagination.currentPage} of ${state.assignment.pagination.lastPage} (${state.assignment.pagination.total} assignment(s))`;
            assignmentPrevBtn.disabled = state.assignment.pagination.currentPage <= 1;
            assignmentNextBtn.disabled = state.assignment.pagination.currentPage >= state.assignment.pagination.lastPage;
        }

        function destroyAssignmentSelects() {
            if (state.assignment.usersSelect) {
                state.assignment.usersSelect.destroy();
                state.assignment.usersSelect = null;
            }
            if (state.assignment.roleSelect) {
                state.assignment.roleSelect.destroy();
                state.assignment.roleSelect = null;
            }
        }

        function initializeAssignmentSelects() {
            destroyAssignmentSelects();

            if (!window.TomSelect) return;

            state.assignment.usersSelect = new TomSelect(assignmentUsersSelect, {
                plugins: ["remove_button"],
                create: false,
                maxOptions: 10000,
                searchField: ["text"],
                placeholder: "Search employees..."
            });

            state.assignment.roleSelect = new TomSelect(assignmentRoleSelect, {
                create: false,
                maxOptions: 1000,
                searchField: ["text"],
                placeholder: "Search role..."
            });
        }

        async function saveAssignments() {
            if (!state.assignment.system?.id) return;

            const selectedUsers = state.assignment.usersSelect
                ? state.assignment.usersSelect.getValue()
                : Array.from(assignmentUsersSelect.selectedOptions).map((item) => item.value);
            const selectedRole = state.assignment.roleSelect
                ? state.assignment.roleSelect.getValue()
                : assignmentRoleSelect.value;

            const userIds = Array.isArray(selectedUsers)
                ? selectedUsers.map((id) => Number(id)).filter((id) => !Number.isNaN(id))
                : (selectedUsers ? [Number(selectedUsers)] : []);

            if (!userIds.length || !selectedRole) {
                showErrorAlert("Please select at least one user and one role.");
                return;
            }

            saveAssignmentsBtn.disabled = true;
            saveAssignmentsBtn.textContent = "Assigning...";

            try {
                const response = await fetch(joinUrl(API_BASE, `central-access/systems/${state.assignment.system.id}/assignments`), {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        user_ids: userIds,
                        level: selectedRole
                    })
                });

                if (!response.ok) throw new Error("Assign failed");
                showSuccessToast("Access assigned successfully.");
                await openAssignAccess(state.assignment.system.id, state.assignment.pagination.currentPage);
            } catch (error) {
                showErrorAlert("Unable to assign access.");
            } finally {
                saveAssignmentsBtn.disabled = false;
                saveAssignmentsBtn.textContent = "Assign";
            }
        }

        async function editAssignment(assignmentId) {
            if (!state.assignment.system?.id) return;

            const roleOptions = {};
            state.assignment.roles.forEach((role) => {
                roleOptions[role.role] = role.role_description
                    ? `${role.role} (${role.role_description})`
                    : role.role;
            });

            const current = state.assignment.rows.find((row) => Number(row.id) === Number(assignmentId));
            const result = await Swal.fire({
                title: "Edit Access Level",
                input: "select",
                inputOptions: roleOptions,
                inputValue: current?.level || "",
                showCancelButton: true,
                confirmButtonText: "Save",
                confirmButtonColor: "#0B132B"
            });

            if (!result.isConfirmed || !result.value) return;

            try {
                const response = await fetch(joinUrl(API_BASE, `central-access/systems/${state.assignment.system.id}/assignments/${assignmentId}`), {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ level: result.value })
                });

                if (!response.ok) throw new Error("Update failed");
                showSuccessToast("Access updated.");
                await openAssignAccess(state.assignment.system.id, state.assignment.pagination.currentPage);
            } catch (error) {
                showErrorAlert("Unable to update access.");
            }
        }

        async function deleteAssignment(assignmentId) {
            if (!state.assignment.system?.id) return;

            const confirmed = await confirmAction("Delete assigned access?", "This assignment will be removed.", "Yes, delete");
            if (!confirmed) return;

            try {
                const response = await fetch(joinUrl(API_BASE, `central-access/systems/${state.assignment.system.id}/assignments/${assignmentId}`), {
                    method: "DELETE",
                    headers: {
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) throw new Error("Delete failed");
                showSuccessToast("Assignment removed.");
                await openAssignAccess(state.assignment.system.id, state.assignment.pagination.currentPage);
            } catch (error) {
                showErrorAlert("Unable to remove assignment.");
            }
        }

        function formatValue(value) {
            if (value === undefined || value === null || value === "") return "-";
            return String(value);
        }

        function formatModuleCellValue(row, column) {
            if (state.activeModule.key === "divisions" && column === "head") {
                return getOptionLabel("users", row.head);
            }

            if (state.activeModule.key === "sections" && column === "head") {
                return getOptionLabel("users", row.head);
            }

            if (state.activeModule.key === "sections" && column === "subsection") {
                return getOptionLabel("sections", row.subsection);
            }

            if (state.activeModule.key === "sections" && column === "division") {
                return getOptionLabel("divisions", row.division);
            }

            if (state.activeModule.key === "device-lists" && column === "userid") {
                return getOptionLabel("users", row.userid);
            }

            if (state.activeModule.key === "access-rights" && column === "system_id") {
                return getOptionLabel("systems", row.system_id);
            }

            return formatValue(row[column]);
        }

        function buildFullName(row) {
            const parts = [row.fname, row.mname, row.lname, row.suffix]
                .map((part) => String(part || "").trim())
                .filter((part) => part !== "");
            if (!parts.length) return "-";
            return parts.join(" ");
        }

        function getUserPictureUrl(row) {
            const picture = String(row.picture || "").trim();
            if (!picture) {
                return `https://ui-avatars.com/api/?name=${encodeURIComponent(buildFullName(row))}&background=0B132B&color=ffffff&size=128`;
            }
            return `https://dohcsmc.com/id/storage/crop/${encodeURIComponent(picture)}`;
        }

        function getOptionLabel(source, idValue) {
            if (idValue === undefined || idValue === null || idValue === "") return "-";
            const found = (state.options[source] || []).find((option) => String(option.id) === String(idValue));
            return found ? found.label : String(idValue);
        }

        function escapeHtml(value) {
            return String(value)
                .replaceAll("&", "&amp;")
                .replaceAll("<", "&lt;")
                .replaceAll(">", "&gt;")
                .replaceAll('"', "&quot;")
                .replaceAll("'", "&#039;");
        }

        function escapeAttr(value) {
            return escapeHtml(value);
        }

        function setGlobalLoading(isLoading, text = "Loading...") {
            const overlay = document.getElementById("globalLoader");
            const label = document.getElementById("globalLoaderText");
            if (!overlay || !label) return;

            label.textContent = text;
            if (isLoading) {
                overlay.classList.remove("hidden");
                overlay.classList.add("flex");
                return;
            }

            overlay.classList.add("hidden");
            overlay.classList.remove("flex");
        }

        async function confirmAction(title, text, confirmText) {
            if (!window.Swal) {
                return window.confirm(text);
            }

            const result = await Swal.fire({
                title,
                text,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: "Cancel",
                confirmButtonColor: "#0B132B",
                cancelButtonColor: "#94a3b8",
                reverseButtons: true
            });

            return result.isConfirmed;
        }

        function showSuccessToast(title) {
            if (!window.Swal) return;
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title,
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true
            });
        }

        function showErrorAlert(text) {
            if (!window.Swal) {
                window.alert(text);
                return;
            }

            Swal.fire({
                icon: "error",
                title: "Request Failed",
                text,
                confirmButtonColor: "#0B132B"
            });
        }

        createBtn.addEventListener("click", openCreate);
        profileMenuBtn.addEventListener("click", () => {
            profileMenu.classList.toggle("hidden");
        });
        openChangePasswordBtn.addEventListener("click", () => {
            profileMenu.classList.add("hidden");
            openPasswordModal();
        });
        closeModalBtn.addEventListener("click", closeModal);
        cancelBtn.addEventListener("click", closeModal);
        saveBtn.addEventListener("click", saveRecord);
        closePasswordModalBtn.addEventListener("click", closePasswordModal);
        cancelPasswordBtn.addEventListener("click", closePasswordModal);
        savePasswordBtn.addEventListener("click", submitPasswordChange);
        closeAssignModalBtn.addEventListener("click", closeAssignAccess);
        saveAssignmentsBtn.addEventListener("click", saveAssignments);
        assignmentSearchInput.addEventListener("input", () => {
            if (!state.assignment.system?.id) return;
            if (state.assignment.searchTimer) {
                clearTimeout(state.assignment.searchTimer);
            }
            state.assignment.searchTimer = setTimeout(() => {
                state.assignment.search = assignmentSearchInput.value;
                openAssignAccess(state.assignment.system.id, 1);
            }, 300);
        });
        assignmentPrevBtn.addEventListener("click", () => {
            if (!state.assignment.system?.id) return;
            if (state.assignment.pagination.currentPage > 1) {
                openAssignAccess(state.assignment.system.id, state.assignment.pagination.currentPage - 1);
            }
        });
        assignmentNextBtn.addEventListener("click", () => {
            if (!state.assignment.system?.id) return;
            if (state.assignment.pagination.currentPage < state.assignment.pagination.lastPage) {
                openAssignAccess(state.assignment.system.id, state.assignment.pagination.currentPage + 1);
            }
        });
        searchInput.addEventListener("input", applySearch);
        prevPageBtn.addEventListener("click", () => {
            if (state.pagination.currentPage > 1) {
                refreshModule(state.pagination.currentPage - 1);
            }
        });
        nextPageBtn.addEventListener("click", () => {
            if (state.pagination.currentPage < state.pagination.lastPage) {
                refreshModule(state.pagination.currentPage + 1);
            }
        });
        formModal.addEventListener("click", (event) => {
            if (event.target === formModal) closeModal();
        });
        assignModal.addEventListener("click", (event) => {
            if (event.target === assignModal) closeAssignAccess();
        });
        passwordModal.addEventListener("click", (event) => {
            if (event.target === passwordModal) closePasswordModal();
        });
        document.addEventListener("click", (event) => {
            if (!profileMenu.contains(event.target) && !profileMenuBtn.contains(event.target)) {
                profileMenu.classList.add("hidden");
            }
        });

        async function initPage() {
            renderModuleNav();
            setGlobalLoading(true, "Preparing module workspace...");
            try {
                await preloadAllOptions();
            } catch (error) {
                console.error("Failed to preload lookup options.", error);
                showErrorAlert("Some lookup options failed to load.");
            }
            await refreshModule();
            setGlobalLoading(false);
        }

        initPage();
    </script>
</body>
</html>
