import { defineConfig } from 'astro/config';
import tailwind from '@astrojs/tailwind';

export default defineConfig({
  site: 'https://electromilitary45.github.io',
  base: '/VaultContrasennas_Laravel',
  integrations: [tailwind()],
  markdown: {
    shikiConfig: {
      theme: 'github-dark',
    },
  },
});
