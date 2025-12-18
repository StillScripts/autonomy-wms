import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

export function PageContainer({
    heading,
    subheading,
    actionButton,
    backUrl,
    children,
}: {
    heading: string;
    subheading: string;
    actionButton?: React.ReactNode;
    backUrl?: string;
    children: React.ReactNode;
}) {
    return (
        <div className="py-12">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="mb-8">
                    <div className="flex items-center justify-between">
                        <div className="flex items-start gap-4">
                            {backUrl && (
                                <Button variant="ghost" size="icon" asChild>
                                    <Link href={backUrl}>
                                        <ArrowLeft className="h-4 w-4" />
                                        <span className="sr-only">Back</span>
                                    </Link>
                                </Button>
                            )}
                            <div>
                                <h2 className="text-2xl font-semibold">{heading}</h2>
                                <p className="text-gray-600">{subheading}</p>
                            </div>
                        </div>
                        {actionButton && <div className="mb-4">{actionButton}</div>}
                    </div>
                </div>
                {children}
            </div>
        </div>
    );
}
