<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('users.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span class="text-sm font-medium">{{ __('Volver a usuarios') }}</span>
                </a>
                <div class="border-l border-gray-300 pl-4">
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                        {{ __('Crear Nuevo Usuario') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Ingrese la información requerida para el nuevo usuario y asigne sus roles.') }}
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="userCreateForm()">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-user-plus text-indigo-600 text-xl"></i>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Información de Registro') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('Todos los campos son obligatorios') }}</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('users.store') }}" class="p-6">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            
                            <div class="form-group">
                                <label for="name" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user text-gray-400 mr-2"></i>
                                    {{ __('Nombre Completo') }}
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" 
                                            name="name" 
                                            id="name" 
                                            value="{{ old('name') }}" 
                                            x-model="form.name"
                                            @blur="validateField('name')"
                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                            :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.name }"
                                            placeholder="{{ __('Ingrese el nombre completo') }}" 
                                            required 
                                            autofocus>
                                    <div x-show="form.name && !errors.name" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i class="fas fa-check text-green-500 text-sm"></i>
                                    </div>
                                </div>
                                @error('name')
                                    <p class="text-sm text-red-600 mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                                <div x-show="errors.name" class="text-sm text-red-600 mt-1 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <span x-text="errors.name"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="username" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-at text-gray-400 mr-2"></i>
                                    {{ __('Nombre de Usuario') }}
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" 
                                            name="username" 
                                            id="username" 
                                            value="{{ old('username') }}" 
                                            x-model="form.username"
                                            @blur="validateField('username')"
                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                            :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.username }"
                                            placeholder="{{ __('Ingrese el nombre de usuario') }}" 
                                            required>
                                    <div x-show="form.username && !errors.username" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i class="fas fa-check text-green-500 text-sm"></i>
                                    </div>
                                </div>
                                @error('username')
                                    <p class="text-sm text-red-600 mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                                <div x-show="errors.username" class="text-sm text-red-600 mt-1 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <span x-text="errors.username"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                    {{ __('Correo Electrónico') }}
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <div class="relative">
                                    <input type="email" 
                                            name="email" 
                                            id="email" 
                                            value="{{ old('email') }}" 
                                            x-model="form.email"
                                            @blur="validateField('email')"
                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                            :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.email }"
                                            placeholder="{{ __('Ingrese el correo electrónico') }}" 
                                            required>
                                    <div x-show="form.email && isValidEmail(form.email)" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i class="fas fa-check text-green-500 text-sm"></i>
                                    </div>
                                </div>
                                @error('email')
                                    <p class="text-sm text-red-600 mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                                <div x-show="errors.email" class="text-sm text-red-600 mt-1 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <span x-text="errors.email"></span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">

                            <div class="form-group">
                                <label for="roles" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-shield-alt text-gray-400 mr-2"></i>
                                    {{ __('Roles del Sistema') }}
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="hidden" name="roles" value="">
                                <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-3 bg-gray-50">
                                    @foreach($roles as $role)
                                        <label class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-md cursor-pointer transition-colors duration-200">
                                            <input type="checkbox" 
                                                    name="roles[]" 
                                                    value="{{ $role->id }}" 
                                                    @checked(is_array(old('roles')) && in_array($role->id, old('roles')))
                                                    x-model="form.roles"
                                                    @change="validateRoles"
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-key text-indigo-500 text-sm"></i>
                                                <span class="text-sm font-medium text-gray-700">{{ $role->name }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('roles')
                                    <p class="text-sm text-red-600 mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                                <div x-show="errors.roles" class="text-sm text-red-600 mt-1 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <span x-text="errors.roles"></span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="password" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-lock text-gray-400 mr-2"></i>
                                    {{ __('Contraseña') }}
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" 
                                            name="password" 
                                            id="password" 
                                            x-model="form.password"
                                            @input="validatePasswords"
                                            @blur="validateField('password')"
                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                            :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.password || passwordMismatch }"
                                            placeholder="{{ __('Ingrese la contraseña') }}" 
                                            required>
                                    <button type="button" 
                                            @click="showPassword = !showPassword" 
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                                        <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="text-sm text-red-600 mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                                <div x-show="errors.password" class="text-sm text-red-600 mt-1 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <span x-text="errors.password"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-lock text-gray-400 mr-2"></i>
                                    {{ __('Confirmar Contraseña') }}
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" 
                                            name="password_confirmation" 
                                            id="password_confirmation" 
                                            x-model="form.password_confirmation"
                                            @input="validatePasswords"
                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                            :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': passwordMismatch }"
                                            placeholder="{{ __('Confirme la contraseña') }}" 
                                            required>
                                    <div x-show="form.password && form.password_confirmation && !passwordMismatch" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i class="fas fa-check text-green-500 text-sm"></i>
                                    </div>
                                </div>
                                <div x-show="passwordMismatch" class="text-sm text-red-600 mt-1 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <span>{{ __('Las contraseñas no coinciden') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            {{ __('Los campos marcados con * son obligatorios') }}
                        </div>
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('users.index') }}" 
                               class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                <i class="fas fa-times mr-2"></i>
                                {{ __('Cancelar') }}
                            </a>
                            <button type="submit" 
                                    :disabled="!isFormValid()"
                                    class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:-translate-y-0.5">
                                <i class="fas fa-user-plus mr-2"></i>
                                {{ __('Crear Usuario') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@push('scripts')
<script>
    function userCreateForm() {
        // Obtenemos los roles seleccionados anteriormente por Blade (old)
        const initialRoles = @json(old('roles', []));
        
        return {
            form: {
                name: '{{ old('name') }}',
                username: '{{ old('username') }}',
                email: '{{ old('email') }}',
                password: '',
                password_confirmation: '',
                // Asegura que los roles se inicialicen como un array
                roles: Array.isArray(initialRoles) ? initialRoles.map(String) : [],
            },
            errors: {},
            showPassword: false,
            passwordMismatch: false,

            init() {
                // Validación inicial de campos si hay 'old' values del backend
                this.validateField('name');
                this.validateField('username');
                this.validateField('email');
                this.validateField('password');
                this.validateRoles();
                // Si hay valores 'old' de contraseña, validamos la coincidencia
                if (this.form.password || this.form.password_confirmation) {
                    this.validatePasswords();
                }
            },

            validateField(field) {
                // Función de validación de campos de texto
                const value = this.form[field].trim();
                
                if (field === 'name') {
                    if (!value) {
                        this.errors.name = 'El nombre es requerido.';
                    } else if (value.length < 2) {
                        this.errors.name = 'El nombre debe tener al menos 2 caracteres.';
                    } else {
                        delete this.errors.name;
                    }
                } else if (field === 'username') {
                    if (!value) {
                        this.errors.username = 'El nombre de usuario es requerido.';
                    } else if (value.length < 3) {
                        this.errors.username = 'El nombre de usuario debe tener al menos 3 caracteres.';
                    } else {
                        delete this.errors.username;
                    }
                } else if (field === 'email') {
                    if (!value) {
                        this.errors.email = 'El correo electrónico es requerido.';
                    } else if (!this.isValidEmail(value)) {
                        this.errors.email = 'Ingrese un correo electrónico válido.';
                    } else {
                        delete this.errors.email;
                    }
                } else if (field === 'password') {
                    if (!value) {
                        this.errors.password = 'La contraseña es requerida.';
                    } else if (value.length < 8) {
                        this.errors.password = 'La contraseña debe tener al menos 8 caracteres.';
                    } else {
                        delete this.errors.password;
                    }
                }
            },

            validatePasswords() {
                this.passwordMismatch = this.form.password !== this.form.password_confirmation;
                
                // Forzar la validación de longitud/presencia del campo principal de password
                this.validateField('password');
            },

            validateRoles() {
                // Para la creación, requerimos al menos 1 rol.
                if (this.form.roles.length === 0) {
                    this.errors.roles = 'Debe seleccionar un rol (Obligatorio).';
                } else {
                    delete this.errors.roles;
                }
            },

            isValidEmail(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            },

            isFormValid() {
                // Ejecutamos validaciones completas antes de verificar
                this.validateField('name');
                this.validateField('username');
                this.validateField('email');
                this.validateField('password');
                this.validatePasswords(); // Verifica coincidencia
                this.validateRoles(); // Verifica al menos un rol

                // Comprobamos que todos los campos requeridos tengan valor
                const requiredFieldsAreFilled = this.form.name.trim() && 
                                                this.form.username.trim() && 
                                                this.form.email.trim() && 
                                                this.form.password.trim() &&
                                                this.form.password_confirmation.trim() &&
                                                this.form.roles.length > 0;

                // Comprobamos que no haya errores de validación en el objeto 'errors'
                const noFieldErrors = Object.keys(this.errors).length === 0;

                // Comprobamos que las contraseñas coincidan
                const noPasswordMismatch = !this.passwordMismatch;
                
                return requiredFieldsAreFilled && noFieldErrors && noPasswordMismatch;
            }
        }
    }
</script>
@endpush
</x-app-layout>