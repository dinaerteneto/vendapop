/**
 * Formats a WhatsApp phone number ensuring it has the country code (55 for Brazil)
 * @param phoneNumber - The phone number to format (can contain formatting characters)
 * @returns The formatted phone number with only digits, starting with 55
 */
export function formatWhatsAppNumber(phoneNumber: string | null | undefined): string {
  if (!phoneNumber) return '';

  // Remove all non-numeric characters
  const digitsOnly = phoneNumber.replace(/[^0-9]/g, '');

  if (!digitsOnly) return '';

  // If number doesn't start with country code 55, add it
  if (!digitsOnly.startsWith('55')) {
    return `55${digitsOnly}`;
  }

  return digitsOnly;
}

