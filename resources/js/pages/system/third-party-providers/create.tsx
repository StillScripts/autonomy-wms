import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import Form from './third-party-provider-form';

export default function Create() {
    return (
        <AppLayout>
            <Head title="Create Third Party Provider" />
            <PageContainer
                heading="Create Third Party Provider"
                subheading="Create a new third party provider"
                backUrl={route('system.third-party-providers.index')}
            >
                <Form mode="create" />
            </PageContainer>
        </AppLayout>
    );
}
