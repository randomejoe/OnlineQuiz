import config from '../config.js'

let handleUnauthorized = async () => { }

export function configureApiClient(options = {}) {
  if (typeof options.handleUnauthorized === 'function') {
    handleUnauthorized = options.handleUnauthorized
  }
}

function buildApiUrl(endpoint) {
  const baseUrl = config.apiDomain.replace(/\/$/, '')
  const path = endpoint.replace(/^\//, '')
  return `${baseUrl}/${path}`
}

async function request(method, endpoint, data, options = {}) {
  const url = buildApiUrl(endpoint)
  const { headers: optionHeaders = {}, ...restOptions } = options

  const headers = {
    ...(data !== undefined ? { 'Content-Type': 'application/json' } : {}),
    ...optionHeaders,
  }

  const response = await fetch(url, {
    method,
    credentials: 'include',
    headers,
    ...(data !== undefined ? { body: JSON.stringify(data) } : {}),
    ...restOptions,
  })

  if (response.status === 401) {
    await handleUnauthorized()
  }

  return response
}

export async function readJsonResponse(response, fallbackMessage) {
  const payload = await response.json().catch(() => ({}))

  if (!response.ok) {
    throw new Error(payload.error || fallbackMessage)
  }

  return payload
}

export function get(endpoint, options = {}) {
  return request('GET', endpoint, undefined, options)
}

export function post(endpoint, data, options = {}) {
  return request('POST', endpoint, data, options)
}

export function put(endpoint, data, options = {}) {
  return request('PUT', endpoint, data, options)
}

export function del(endpoint, options = {}) {
  return request('DELETE', endpoint, undefined, options)
}

export function getApiUrl(endpoint) {
  return buildApiUrl(endpoint)
}
