import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

/* Builds the bundle into ../assets. File names carry a content hash for
   cache-busting; includes/layout.php looks them up in the manifest. */
export default defineConfig({
  plugins: [react()],
  base: './',
  build: {
    outDir: '../assets',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: 'src/main.jsx',
      output: {
        entryFileNames: 'app-[hash].js',
        chunkFileNames: 'chunks/[name]-[hash].js',
        assetFileNames: 'app-[hash].[ext]',
      },
    },
  },
});
