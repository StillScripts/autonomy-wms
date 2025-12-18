import { useFormContext } from '@/components/forms/form-context';
import { Button } from '@/components/ui/button';
import { LoaderIcon } from 'lucide-react';

/**
 * A button that subscribes to the form state and displays a loading spinner when the form is submitting
 * @param label - The button text
 * @returns A button that subscribes to the form state and displays a loading spinner when the form is submitting
 */
export function SubscribeButton({ label }: { label: string }) {
    const form = useFormContext();
    return (
        <form.Subscribe selector={(state) => [state.canSubmit, state.isSubmitting]}>
            {([canSubmit, isSubmitting]) => (
                <Button
                    type={isSubmitting ? 'button' : 'submit'}
                    aria-disabled={isSubmitting || !canSubmit}
                    disabled={isSubmitting || !canSubmit}
                    className="relative w-full"
                >
                    {label}
                    {isSubmitting && (
                        <span className="absolute right-4 animate-spin">
                            <LoaderIcon />
                        </span>
                    )}
                    <span aria-live="polite" className="sr-only" role="status">
                        {isSubmitting ? 'Loading' : label}
                    </span>
                </Button>
            )}
        </form.Subscribe>
    );
}
