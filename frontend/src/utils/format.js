export function formatDateTime(value) {
  if (!value) return '-'
  return new Date(value).toLocaleString()
}

export function formatDateOnly(value) {
  if (!value) return '-'
  return new Date(value).toLocaleDateString()
}

export function formatPercentage(value, fractionDigits = 1) {
  const number = Number(value)
  if (Number.isNaN(number)) {
    return `${(0).toFixed(fractionDigits)}`
  }

  return number.toFixed(fractionDigits)
}
