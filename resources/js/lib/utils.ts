import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function capitalize(str: string, everyWord = false) {
    if (everyWord) {
        return str
            .split(' ')
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }
    return str.charAt(0).toUpperCase() + str.slice(1);
}

export function money(amount: number | string) {
    if (amount === 0) {
        return '$0.00';
    }
    if (!amount) {
        return null;
    }
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(typeof amount === 'string' ? parseFloat(amount) : amount);
}
