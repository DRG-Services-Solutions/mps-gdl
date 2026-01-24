import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/pages/surgeries/picking-rfid.js',
            ],
            refresh: true,
        }),
    ],
    
    server: {
        host: '0.0.0.0',           
        port: 5173,                
        strictPort: true,          
        
        cors: {
            origin: '*',           
            methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
            allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With'],
            credentials: true
        },
        
        hmr: {
            host: '192.168.1.223',   // ip 
            port: 5173,
            protocol: 'ws'         // WebSocket para HMR
        },
        
        watch: {
            usePolling: true,      
        }
    },
});