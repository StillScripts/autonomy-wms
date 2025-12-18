import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import { ThirdPartyProvider, ThirdPartyVariable } from '@/types/models';
import { Head } from '@inertiajs/react';
import Form from './third-party-variable-form';

interface Props {
    variable: ThirdPartyVariable;
    providers: ThirdPartyProvider[];
}

export default function Edit({ variable, providers }: Props) {
    return (
        <AppLayout>
            <Head title={`Edit ${variable.name}`} />
            <PageContainer
                heading={`Edit ${variable.name}`}
                subheading="Edit the details of your variable"
                backUrl={route('system.third-party-variables.index')}
            >
                <Form
                    mode="edit"
                    variable={variable}
                    providers={providers}
                    defaultValues={{
                        name: variable.name,
                        description: variable.description || '',
                        is_secret: variable.is_secret,
                        third_party_provider_id: variable.third_party_provider_id.toString(),
                    }}
                />
            </PageContainer>
        </AppLayout>
    );
}
