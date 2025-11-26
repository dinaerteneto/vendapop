/**
 * Formats a number as Brazilian Real currency (R$)
 * Adds thousand separators (.) and decimal separator (,)
 * @param value - The numeric value to format
 * @returns Formatted string like "R$ 450.000,00"
 */
export function formatCurrency(value: number | string): string {
  const numValue = typeof value === 'string' ? parseFloat(value) : value;
  
  if (isNaN(numValue)) return 'R$ 0,00';
  
  // Format with 2 decimal places
  const formatted = numValue.toFixed(2);
  
  // Split integer and decimal parts
  const [integerPart, decimalPart] = formatted.split('.');
  
  // Add thousand separators
  const integerWithSeparators = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  
  return `R$ ${integerWithSeparators},${decimalPart}`;
}

