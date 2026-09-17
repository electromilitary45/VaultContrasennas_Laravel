import { defineConfig } from 'astro/config';
import tailwind from '@astrojs/tailwind';

export default defineConfig({
  site: 'https://vault.villalobossebas.me',
  base: '/',
  integrations: [tailwind()],
  markdown: {
    shikiConfig: {
      theme: 'github-dark',
    },
  },
});
