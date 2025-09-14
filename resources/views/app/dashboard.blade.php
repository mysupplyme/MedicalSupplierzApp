<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Suppliers - Web App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-50">
    <div id="app">
        <!-- Navigation -->
        <nav class="bg-blue-600 text-white p-4 shadow-lg">
            <div class="container mx-auto flex justify-between items-center">
                <h1 class="text-xl font-bold">Medical Suppliers</h1>
                <div class="flex space-x-4">
                    <button @click="currentView = 'profile'" class="hover:bg-blue-700 px-3 py-1 rounded">Profile</button>
                    <button v-if="user" @click="logout" class="hover:bg-blue-700 px-3 py-1 rounded">Logout</button>
                    <a v-else href="/login" class="hover:bg-blue-700 px-3 py-1 rounded">Login</a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container mx-auto p-4">
            <!-- Dashboard View -->
            <div v-if="currentView === 'dashboard'" class="space-y-8">
                <!-- Main Categories (Conferences & Expos) -->
                <div>
                    <h2 class="text-2xl font-bold mb-6 text-gray-800">Medical Events</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer" @click="loadSubCategories(3021, 'Conferences')">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                    <span class="text-2xl">🏥</span>
                                </div>
                                <h3 class="text-xl font-semibold text-blue-600">Conferences</h3>
                            </div>
                            <p class="text-gray-600">Medical conferences and professional events</p>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer" @click="loadSubCategories(3022, 'Expos')">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                    <span class="text-2xl">🏢</span>
                                </div>
                                <h3 class="text-xl font-semibold text-green-600">Expos</h3>
                            </div>
                            <p class="text-gray-600">Medical exhibitions and trade shows</p>
                        </div>
                    </div>
                </div>
                
                <!-- Other Features -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                        <h2 class="text-lg font-semibold mb-4 text-green-600">Products</h2>
                        <p class="text-gray-600 mb-4">View available medical products</p>
                        <button @click="loadProducts" class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600 transition-colors">
                            View Products
                        </button>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                        <h2 class="text-lg font-semibold mb-4 text-purple-600">Events</h2>
                        <p class="text-gray-600 mb-4">Medical conferences and events</p>
                        <button @click="loadEvents" class="w-full bg-purple-500 text-white py-2 rounded hover:bg-purple-600 transition-colors">
                            Browse Events
                        </button>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                        <h2 class="text-lg font-semibold mb-4 text-orange-600">Subscriptions</h2>
                        <p class="text-gray-600 mb-4">Manage your subscriptions</p>
                        <button @click="loadSubscriptions" class="w-full bg-orange-500 text-white py-2 rounded hover:bg-orange-600 transition-colors">
                            My Subscriptions
                        </button>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                        <h2 class="text-lg font-semibold mb-4 text-red-600">Suppliers</h2>
                        <p class="text-gray-600 mb-4">Find medical suppliers</p>
                        <button @click="loadSuppliers" class="w-full bg-red-500 text-white py-2 rounded hover:bg-red-600 transition-colors">
                            Find Suppliers
                        </button>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                        <h2 class="text-lg font-semibold mb-4 text-gray-600">Doctors</h2>
                        <p class="text-gray-600 mb-4">Connect with doctors</p>
                        <button @click="loadDoctors" class="w-full bg-gray-500 text-white py-2 rounded hover:bg-gray-600 transition-colors">
                            Find Doctors
                        </button>
                    </div>
                </div>

                <!-- Additional Mobile APIs -->
                <div>
                    <h3 class="text-xl font-bold mb-4 text-gray-800">Additional Features</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white rounded-lg shadow p-4">
                            <h4 class="font-semibold mb-2 text-indigo-600">Specialties</h4>
                            <button @click="loadSpecialties" class="w-full bg-indigo-500 text-white py-1 rounded text-sm hover:bg-indigo-600">
                                View Specialties
                            </button>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4">
                            <h4 class="font-semibold mb-2 text-teal-600">Conferences</h4>
                            <button @click="loadConferences" class="w-full bg-teal-500 text-white py-1 rounded text-sm hover:bg-teal-600">
                                View Conferences
                            </button>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4">
                            <h4 class="font-semibold mb-2 text-pink-600">Subscription Plans</h4>
                            <button @click="loadSubscriptionPlans" class="w-full bg-pink-500 text-white py-1 rounded text-sm hover:bg-pink-600">
                                View Plans
                            </button>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4">
                            <h4 class="font-semibold mb-2 text-yellow-600">Terms & Conditions</h4>
                            <button @click="loadTerms" class="w-full bg-yellow-500 text-white py-1 rounded text-sm hover:bg-yellow-600">
                                View Terms
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Profile Management -->
                <div>
                    <h3 class="text-xl font-bold mb-4 text-gray-800">Profile Management</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white rounded-lg shadow p-4">
                            <h4 class="font-semibold mb-2 text-blue-600">Update Profile</h4>
                            <button @click="currentView = 'update-profile'" class="w-full bg-blue-500 text-white py-1 rounded text-sm hover:bg-blue-600">
                                Edit Profile
                            </button>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4">
                            <h4 class="font-semibold mb-2 text-red-600">Change Password</h4>
                            <button @click="currentView = 'change-password'" class="w-full bg-red-500 text-white py-1 rounded text-sm hover:bg-red-600">
                                Change Password
                            </button>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4">
                            <h4 class="font-semibold mb-2 text-green-600">Subscription Status</h4>
                            <button @click="checkSubscriptionStatus" class="w-full bg-green-500 text-white py-1 rounded text-sm hover:bg-green-600">
                                Check Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sub Categories View -->
            <div v-if="currentView === 'subcategories'" class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold">{{ selectedCategoryName }}</h2>
                        <p class="text-gray-600">Select a specialty</p>
                    </div>
                    <button @click="currentView = 'dashboard'" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Back to Dashboard
                    </button>
                </div>
                <div v-if="loading" class="text-center py-8">Loading...</div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="category in categories" :key="category.id" 
                         class="bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-blue-300 hover:shadow-md cursor-pointer transition-all"
                         @click="loadSubSubCategories(category.id, category.name)">
                        <h3 class="font-semibold text-lg mb-2">{{ category.name }}</h3>
                        <p class="text-gray-600 text-sm">{{ category.description || 'Click to view sub-specialties' }}</p>
                        <div class="mt-3 text-blue-500 text-sm font-medium">View Sub-specialties →</div>
                    </div>
                </div>
            </div>
            
            <!-- Sub-Sub Categories View -->
            <div v-if="currentView === 'subsubcategories'" class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold">{{ selectedSubCategoryName }}</h2>
                        <p class="text-gray-600">Sub-specialties</p>
                    </div>
                    <div class="space-x-2">
                        <button @click="goBackToSubCategories" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">
                            Back
                        </button>
                        <button @click="currentView = 'dashboard'" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Dashboard
                        </button>
                    </div>
                </div>
                <div v-if="loading" class="text-center py-8">Loading...</div>
                <div v-else-if="subSubCategories.length === 0" class="text-center py-8 text-gray-500">
                    No sub-specialties available
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="category in subSubCategories" :key="category.id" 
                         class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4 hover:shadow-md cursor-pointer transition-all">
                        <h3 class="font-semibold text-md mb-1">{{ category.name }}</h3>
                        <p class="text-gray-600 text-xs">{{ category.description || 'Specialty area' }}</p>
                    </div>
                </div>
            </div>

            <!-- Products View -->
            <div v-if="currentView === 'products'" class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">{{ selectedCategoryName || 'Products' }}</h2>
                    <button @click="currentView = selectedCategoryName ? 'categories' : 'dashboard'" 
                            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Back
                    </button>
                </div>
                <div v-if="loading" class="text-center py-8">Loading...</div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="product in products" :key="product.id" class="border rounded-lg p-4">
                        <h3 class="font-semibold">{{ product.name }}</h3>
                        <p class="text-gray-600 text-sm mb-2">{{ product.description || 'No description' }}</p>
                        <p v-if="product.price" class="text-green-600 font-medium">${{ product.price }}</p>
                        <button @click="loadProductSuppliers(product.id, product.name)" 
                                class="mt-2 bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                            View Suppliers
                        </button>
                    </div>
                </div>
            </div>

            <!-- Events View -->
            <div v-if="currentView === 'events'" class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Medical Events</h2>
                    <button @click="currentView = 'dashboard'" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Back to Dashboard
                    </button>
                </div>
                <div v-if="loading" class="text-center py-8">Loading...</div>
                <div v-else class="space-y-4">
                    <div v-for="event in events" :key="event.id" class="border rounded-lg p-6">
                        <h3 class="font-semibold text-xl mb-2">{{ event.title }}</h3>
                        <p class="text-gray-600 mb-3">{{ event.description }}</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-500 mb-4">
                            <div><strong>Date:</strong> {{ event.date }}</div>
                            <div><strong>Time:</strong> {{ event.time }}</div>
                            <div><strong>Duration:</strong> {{ event.duration }}</div>
                            <div><strong>Price:</strong> ${{ event.price || 'Free' }}</div>
                        </div>
                        <button v-if="user" @click="registerEvent(event.id)" 
                                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            Register for Event
                        </button>
                        <p v-else class="text-gray-500">Please login to register</p>
                    </div>
                </div>
            </div>

            <!-- Profile View -->
            <div v-if="currentView === 'profile'" class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Profile</h2>
                    <button @click="currentView = 'dashboard'" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Back to Dashboard
                    </button>
                </div>
                <div v-if="!user" class="text-center py-8">
                    <p class="text-gray-500 mb-4">Please login to view your profile</p>
                    <a href="/login" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Login</a>
                </div>
                <div v-else-if="loading" class="text-center py-8">Loading...</div>
                <div v-else class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.phone || 'Not provided' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.role }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Specialty</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.speciality || 'Not specified' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sub-Specialty</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.sub_speciality || 'Not specified' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Profile View -->
            <div v-if="currentView === 'update-profile'" class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Update Profile</h2>
                    <button @click="currentView = 'dashboard'" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Back to Dashboard
                    </button>
                </div>
                <form @submit.prevent="updateProfile" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input v-model="profileForm.name" type="text" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input v-model="profileForm.phone" type="text" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Specialty</label>
                            <input v-model="profileForm.speciality" type="text" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sub-Specialty</label>
                            <input v-model="profileForm.sub_speciality" type="text" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Update Profile
                    </button>
                </form>
            </div>

            <!-- Change Password View -->
            <div v-if="currentView === 'change-password'" class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Change Password</h2>
                    <button @click="currentView = 'dashboard'" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Back to Dashboard
                    </button>
                </div>
                <form @submit.prevent="changePassword" class="space-y-4 max-w-md">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Current Password</label>
                        <input v-model="passwordForm.current_password" type="password" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">New Password</label>
                        <input v-model="passwordForm.new_password" type="password" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <input v-model="passwordForm.new_password_confirmation" type="password" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" required>
                    </div>
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Change Password
                    </button>
                </form>
            </div>

            <!-- Generic List View -->
            <div v-if="currentView === 'list'" class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">{{ listTitle }}</h2>
                    <button @click="currentView = 'dashboard'" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Back to Dashboard
                    </button>
                </div>
                <div v-if="loading" class="text-center py-8">Loading...</div>
                <div v-else class="space-y-4">
                    <div v-for="item in listData" :key="item.id" class="border rounded-lg p-4">
                        <h3 class="font-semibold">{{ item.name || item.title }}</h3>
                        <p class="text-gray-600 text-sm">{{ item.description || item.content || 'No description' }}</p>
                        <div v-if="item.price" class="text-green-600 font-medium">${{ item.price }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    currentView: 'dashboard',
                    loading: false,
                    user: null,
                    categories: [],
                    products: [],
                    events: [],
                    subscriptions: [],
                    suppliers: [],
                    doctors: [],
                    selectedCategoryName: null,
                selectedSubCategoryName: null,
                selectedParentId: null,
                subSubCategories: [],
                    authToken: localStorage.getItem('auth_token'),
                    listData: [],
                    listTitle: '',
                    profileForm: {
                        name: '',
                        phone: '',
                        speciality: '',
                        sub_speciality: ''
                    },
                    passwordForm: {
                        current_password: '',
                        new_password: '',
                        new_password_confirmation: ''
                    }
                }
            },
            mounted() {
                if (this.authToken) {
                    this.loadProfile();
                }
            },
            watch: {
                user(newUser) {
                    if (newUser) {
                        this.profileForm = {
                            name: newUser.name || '',
                            phone: newUser.phone || '',
                            speciality: newUser.speciality || '',
                            sub_speciality: newUser.sub_speciality || ''
                        };
                    }
                }
            },
            methods: {
                async apiCall(endpoint, method = 'GET', data = null) {
                    const config = {
                        method,
                        url: `/api${endpoint}`,
                        headers: this.authToken ? { 'Authorization': `Bearer ${this.authToken}` } : {}
                    };
                    if (data) config.data = data;
                    return axios(config);
                },

                async loadProfile() {
                    try {
                        const response = await this.apiCall('/get_profile');
                        this.user = response.data.data || response.data;
                    } catch (error) {
                        console.error('Profile load error:', error);
                        localStorage.removeItem('auth_token');
                        this.authToken = null;
                    }
                },

                async loadSubCategories(parentId, categoryName) {
                    this.loading = true;
                    this.currentView = 'subcategories';
                    this.selectedCategoryName = categoryName;
                    this.selectedParentId = parentId;
                    try {
                        const response = await this.apiCall(`/lists/categories?parent_id=${parentId}`);
                        this.categories = response.data.data || response.data;
                    } catch (error) {
                        console.error('Sub-categories load error:', error);
                        alert('Please login to view categories');
                        this.currentView = 'dashboard';
                    }
                    this.loading = false;
                },
                
                async loadSubSubCategories(parentId, categoryName) {
                    this.loading = true;
                    this.currentView = 'subsubcategories';
                    this.selectedSubCategoryName = categoryName;
                    try {
                        const response = await this.apiCall(`/lists/categories?parent_id=${parentId}`);
                        this.subSubCategories = response.data.data || response.data;
                    } catch (error) {
                        console.error('Sub-sub-categories load error:', error);
                        this.subSubCategories = [];
                    }
                    this.loading = false;
                },
                
                goBackToSubCategories() {
                    this.loadSubCategories(this.selectedParentId, this.selectedCategoryName);
                },

                async loadCategoryProducts(categoryId, categoryName) {
                    this.loading = true;
                    this.currentView = 'products';
                    this.selectedCategoryName = categoryName;
                    try {
                        const response = await this.apiCall(`/common/category-products/${categoryId}`);
                        this.products = response.data.data || response.data;
                    } catch (error) {
                        console.error('Products load error:', error);
                    }
                    this.loading = false;
                },

                async loadProducts() {
                    this.loading = true;
                    this.currentView = 'products';
                    this.selectedCategoryName = null;
                    try {
                        const response = await this.apiCall('/v1/products');
                        this.products = response.data.data || response.data;
                    } catch (error) {
                        console.error('Products load error:', error);
                    }
                    this.loading = false;
                },

                async loadProductSuppliers(productId, productName) {
                    try {
                        const response = await this.apiCall(`/common/product-suppliers/${productId}`);
                        const suppliers = response.data.data || response.data;
                        alert(`Suppliers for ${productName}: ${suppliers.length} found`);
                    } catch (error) {
                        console.error('Suppliers load error:', error);
                    }
                },

                async loadEvents() {
                    this.loading = true;
                    this.currentView = 'events';
                    try {
                        const response = await this.apiCall('/events');
                        this.events = response.data.data || response.data;
                    } catch (error) {
                        console.error('Events load error:', error);
                    }
                    this.loading = false;
                },

                async loadSubscriptions() {
                    if (!this.authToken) {
                        alert('Please login to view subscriptions');
                        return;
                    }
                    this.loading = true;
                    try {
                        const response = await this.apiCall('/my-subscriptions');
                        this.subscriptions = response.data.data || response.data;
                        this.currentView = 'subscriptions';
                    } catch (error) {
                        console.error('Subscriptions load error:', error);
                        alert('Please login to view subscriptions');
                    }
                    this.loading = false;
                },

                async loadSuppliers() {
                    this.loading = true;
                    try {
                        const response = await this.apiCall('/common/clients');
                        this.suppliers = response.data.data || response.data;
                        alert(`Found ${this.suppliers.length} suppliers`);
                    } catch (error) {
                        console.error('Suppliers load error:', error);
                    }
                    this.loading = false;
                },

                async loadDoctors() {
                    this.loading = true;
                    try {
                        const response = await this.apiCall('/common/doctors');
                        this.doctors = response.data.data || response.data;
                        alert(`Found ${this.doctors.length} doctors`);
                    } catch (error) {
                        console.error('Doctors load error:', error);
                    }
                    this.loading = false;
                },

                async registerEvent(eventId) {
                    if (!this.authToken) {
                        alert('Please login to register for events');
                        return;
                    }
                    try {
                        await this.apiCall(`/events/${eventId}/register`, 'POST');
                        alert('Successfully registered for event!');
                    } catch (error) {
                        console.error('Event registration error:', error);
                        alert('Failed to register for event');
                    }
                },

                async loadSpecialties() {
                    this.loading = true;
                    this.currentView = 'list';
                    this.listTitle = 'Medical Specialties';
                    try {
                        const response = await this.apiCall('/common/specialties');
                        this.listData = response.data.data || response.data;
                    } catch (error) {
                        console.error('Specialties load error:', error);
                    }
                    this.loading = false;
                },

                async loadConferences() {
                    this.loading = true;
                    this.currentView = 'list';
                    this.listTitle = 'Medical Conferences';
                    try {
                        const response = await this.apiCall('/common/conferences');
                        this.listData = response.data.data || response.data;
                    } catch (error) {
                        console.error('Conferences load error:', error);
                    }
                    this.loading = false;
                },

                async loadSubscriptionPlans() {
                    this.loading = true;
                    this.currentView = 'list';
                    this.listTitle = 'Subscription Plans';
                    try {
                        const response = await this.apiCall('/subscription-plans');
                        this.listData = response.data.data || response.data;
                    } catch (error) {
                        console.error('Subscription plans load error:', error);
                    }
                    this.loading = false;
                },

                async loadTerms() {
                    this.loading = true;
                    this.currentView = 'list';
                    this.listTitle = 'Terms & Conditions';
                    try {
                        const response = await this.apiCall('/terms-conditions');
                        this.listData = [response.data.data];
                    } catch (error) {
                        console.error('Terms load error:', error);
                    }
                    this.loading = false;
                },

                async updateProfile() {
                    try {
                        await this.apiCall('/update_profile', 'PUT', this.profileForm);
                        alert('Profile updated successfully!');
                        this.loadProfile();
                        this.currentView = 'profile';
                    } catch (error) {
                        console.error('Profile update error:', error);
                        alert('Failed to update profile');
                    }
                },

                async changePassword() {
                    if (this.passwordForm.new_password !== this.passwordForm.new_password_confirmation) {
                        alert('New passwords do not match');
                        return;
                    }
                    try {
                        await this.apiCall('/change_password', 'POST', this.passwordForm);
                        alert('Password changed successfully!');
                        this.passwordForm = { current_password: '', new_password: '', new_password_confirmation: '' };
                        this.currentView = 'dashboard';
                    } catch (error) {
                        console.error('Password change error:', error);
                        alert('Failed to change password');
                    }
                },

                async checkSubscriptionStatus() {
                    try {
                        const response = await this.apiCall('/subscription-status');
                        const status = response.data.data || response.data;
                        alert(`Subscription Status: ${status.status || 'No active subscription'}`);
                    } catch (error) {
                        console.error('Subscription status error:', error);
                        alert('Failed to check subscription status');
                    }
                },

                logout() {
                    localStorage.removeItem('auth_token');
                    this.authToken = null;
                    this.user = null;
                    window.location.href = '/login';
                }
            }
        }).mount('#app');
    </script>
</body>
</html>