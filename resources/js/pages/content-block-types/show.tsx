import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import { ContentBlockTypesForm } from '@/pages/content-block-types/content-block-types-form';
import type { ContentBlockType, Organisation } from '@/types/models';
import { Head } from '@inertiajs/react';

interface ShowContentBlockTypePageProps {
    contentBlockType: ContentBlockType;
    organisation: Organisation;
    customContentBlockTypeOptions: { label: string; id: string; reference_block_type_id: string }[];
}

export default function ShowContentBlockType({ contentBlockType, organisation, customContentBlockTypeOptions }: ShowContentBlockTypePageProps) {
    return (
        <AppLayout>
            <Head title={`${contentBlockType.name} - Content Block Type`} />
            <PageContainer
                backUrl={route('content-block-types.index')}
                heading={`${organisation.name} - ${contentBlockType.name}`}
                subheading="View and edit content block type"
            >
                <ContentBlockTypesForm
                    mode="edit"
                    defaultValues={{
                        name: contentBlockType.name,
                        is_default: contentBlockType.is_default,
                        fields: contentBlockType.fields.map((field) =>
                            field.reference_block_type_id
                                ? {
                                      ...field,
                                      type: field.reference_block_type_id,
                                  }
                                : field,
                        ),
                    }}
                    contentBlockTypeSlug={contentBlockType.slug}
                    customContentBlockTypeOptions={customContentBlockTypeOptions}
                />
            </PageContainer>
        </AppLayout>
    );
}
