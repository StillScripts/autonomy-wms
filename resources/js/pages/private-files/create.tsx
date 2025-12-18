import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import PrivateFileForm from '@/pages/private-files/private-file-form';
import { Organisation } from '@/types/models';
import { Head } from '@inertiajs/react';

interface PrivateFilesCreateProps {
    organisation: Organisation;
    contentTypes: string[];
}

export default function PrivateFilesCreate({ organisation, contentTypes }: PrivateFilesCreateProps) {
    return (
        <AppLayout>
            <Head title="Upload Private File" />
            <PageContainer
                backUrl={route('private-files.index')}
                heading="Upload Private File"
                subheading={`Add a new private file to ${organisation.name}`}
            >
                <PrivateFileForm mode="create" contentTypes={contentTypes} />
            </PageContainer>
        </AppLayout>
    );
}
