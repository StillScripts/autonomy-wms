import { PageContainer } from '@/components/page-container';
import { Button } from '@/components/ui/button';
import { Card, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';

export default function PagesIndex() {
    return (
        <AppLayout>
            <Head title="System Settings" />
            <PageContainer heading="System Settings" subheading="Manage the system settings">
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <CardTitle>Third Party Providers</CardTitle>
                        </CardHeader>
                        <CardFooter>
                            <Button asChild>
                                <Link href={route('system.third-party-providers.index')}>Manage Providers</Link>
                            </Button>
                        </CardFooter>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>Third Party Variables</CardTitle>
                        </CardHeader>
                        <CardFooter>
                            <Button asChild>
                                <Link href={route('system.third-party-variables.index')}>Manage Variables</Link>
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
            </PageContainer>
        </AppLayout>
    );
}
