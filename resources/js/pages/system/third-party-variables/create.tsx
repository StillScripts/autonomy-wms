import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import { ThirdPartyProvider } from '@/types/models';
import { Head } from '@inertiajs/react';
import Form from './third-party-variable-form';

interface Props {
    providers: ThirdPartyProvider[];
}

export default function Create({ providers }: Props) {
    return (
        <AppLayout>
            <Head title="Create Third Party Variable" />
            <PageContainer
                heading="Create Third Party Variable"
                subheading="Create a new third party variable"
                backUrl={route('system.third-party-variables.index')}
            >
                <Form mode="create" providers={providers} />
            </PageContainer>
        </AppLayout>
    );
}
