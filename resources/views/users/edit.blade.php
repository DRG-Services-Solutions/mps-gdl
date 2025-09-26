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
                        {{ __('Editar Usuario') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Modificar la información del usuario') }} {{ $user->name }}
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="userEditForm()">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- User Info Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="h-16 w-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                <span class="text-xl font-bold text-white">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Editando información de') }}</h3>
                            <p class="text-indigo-600 font-medium">{{ $user->name }}</p>
                            <p class="text-sm text-gray-600">{{ __('Miembro desde') }} {{ $user->created_at->format('d/m/Y') }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-circle mr-1.5 text-xs"></i>
                                {{ __('Usuario Activo') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-user-edit text-indigo-600 text-xl"></i>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Información del Usuario') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('Complete todos los campos requeridos') }}</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('users.update', $user->id) }}" class="p-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Name Field -->
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
                                           value="{{ old('name', $user->name) }}" 
                                           x-model="form.name"
                                           @blur="validateField('name')"
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                           :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.name }"
                                           placeholder="{{ __('Ingrese el nombre completo') }}" 
                                           required 
                                           autofocus>
                                    <div x-show="form.name" class="absolute inset-y-0 right-0 flex items-center pr-3">
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

                            <!-- Username Field -->
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
                                           value="{{ old('username', $user->username) }}" 
                                           x-model="form.username"
                                           @blur="validateField('username')"
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                           :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.username }"
                                           placeholder="{{ __('Ingrese el nombre de usuario') }}" 
                                           required>
                                    <div x-show="form.username" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i class="fas fa-check text-green-500 text-sm"></i>
                                    </div>
                                </div>
                                @error('username')
                                    <p class="text-sm text-red-600 mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Email Field -->
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
                                           value="{{ old('email', $user->email) }}" 
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
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Roles Field -->
                            <div class="form-group">
                                <label for="roles" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-shield-alt text-gray-400 mr-2"></i>
                                    {{ __('Roles del Sistema') }}
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-3 bg-gray-50">
                                    @foreach($roles as $role)
                                        <label class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-md cursor-pointer transition-colors duration-200">
                                            <input type="checkbox" 
                                                   name="roles[]" 
                                                   value="{{ $role->id }}" 
                                                   @if($user->hasRole($role->name)) checked @endif
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
                            </div>

                            <!-- Password Section -->
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-info-circle text-yellow-600 mt-0.5"></i>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-yellow-800">{{ __('Cambio de Contraseña') }}</h4>
                                        <p class="text-sm text-yellow-700 mt-1">{{ __('Deje los campos vacíos si no desea cambiar la contraseña actual') }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Field -->
                            <div class="form-group">
                                <label for="password" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-lock text-gray-400 mr-2"></i>
                                    {{ __('Nueva Contraseña') }}
                                    <span class="text-gray-500 text-xs ml-2">({{ __('Opcional') }})</span>
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           x-model="form.password"
                                           @input="validatePasswords"
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                           placeholder="{{ __('Ingrese la nueva contraseña') }}">
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
                            </div>

                            <!-- Password Confirmation Field -->
                            <div class="form-group" x-show="form.password">
                                <label for="password_confirmation" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-lock text-gray-400 mr-2"></i>
                                    {{ __('Confirmar Nueva Contraseña') }}
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           name="password_confirmation" 
                                           id="password_confirmation" 
                                           x-model="form.password_confirmation"
                                           @input="validatePasswords"
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                           :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': passwordMismatch }"
                                           placeholder="{{ __('Confirme la nueva contraseña') }}">
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

                    <!-- Form Actions -->
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
                                <i class="fas fa-save mr-2"></i>
                                {{ __('Actualizar Usuario') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function userEditForm() {
            return {
                form: {
                    name: '{{ old('name', $user->name) }}',
                    username: '{{ old('username', $user->username) }}',
                    email: '{{ old('email', $user->email) }}',
                    password: '',
                    password_confirmation: ''
                },
                errors: {},
                showPassword: false,
                passwordMismatch: false,

                validateField(field) {
                    switch(field) {
                        case 'name':
                            if (!this.form.name.trim()) {
                                this.errors.name = 'El nombre es requerido';
                            } else if (this.form.name.length < 2) {
                                this.errors.name = 'El nombre debe tener al menos 2 caracteres';
                            } else {
                                delete this.errors.name;
                            }
                            break;
                        case 'username':
                            if (!this.form.username.trim()) {
                                this.errors.username = 'El nombre de usuario es requerido';
                            } else if (this.form.username.length < 3) {
                                this.errors.username = 'El nombre de usuario debe tener al menos 3 caracteres';
                            } else {
                                delete this.errors.username;
                            }
                            break;
                        case 'email':
                            if (!this.form.email.trim()) {
                                this.errors.email = 'El correo electrónico es requerido';
                            } else if (!this.isValidEmail(this.form.email)) {
                                this.errors.email = 'Ingrese un correo electrónico válido';
                            } else {
                                delete this.errors.email;
                            }
                            break;
                    }
                },

                validatePasswords() {
                    if (this.form.password && this.form.password_confirmation) {
                        this.passwordMismatch = this.form.password !== this.form.password_confirmation;
                    } else {
                        this.passwordMismatch = false;
                    }
                },

                isValidEmail(email) {
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
                },

                isFormValid() {
                    return this.form.name.trim() && 
                           this.form.username.trim() && 
                           this.form.email.trim() && 
                           this.isValidEmail(this.form.email) && 
                           Object.keys(this.errors).length === 0 &&
                           !this.passwordMismatch;
                }
            }
        }
    </script>
    
</x-app-layout>