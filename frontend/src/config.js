/**
 * Application Configuration
 * 
 * Configuration values can be overridden using environment variables.
 * For Vite, use VITE_ prefix for environment variables.
 * 
 * Create a .env file in the frontend root to override defaults:
 * VITE_API_DOMAIN=http://localhost:8000
 */

const config = {
  /**
   * API Domain - Base URL for API requests
   * @type {string}
   * @default 'http://localhost'
   */
  apiDomain: import.meta.env.VITE_API_DOMAIN || 'http://localhost',

};

// Freeze config to prevent accidental mutations
export default Object.freeze(config);
