import { PageContainer } from '@/components/page-container';
import { ContentBlockTypesForm } from './content-block-types-form';

import AppLayout from '@/layouts/app-layout';
import type { Organisation } from '@/types/models';
import { Head } from '@inertiajs/react';

interface CreateContentBlockTypePageProps {
    organisation: Organisation;
    customContentBlockTypeOptions: { label: string; id: string }[];
}

export default function CreateContentBlockType({ organisation, customContentBlockTypeOptions }: CreateContentBlockTypePageProps) {
    return (
        <AppLayout>
            <Head title="Create Content Block Type" />
            <PageContainer heading={`${organisation.name} - Create Content Block Type`} subheading="Create a new content block type">
                <ContentBlockTypesForm mode="create" customContentBlockTypeOptions={customContentBlockTypeOptions} />
            </PageContainer>
        </AppLayout>
    );
}
