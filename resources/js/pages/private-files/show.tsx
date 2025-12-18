import { PageContainer } from '@/components/page-container';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { PrivateFile } from '@/types/models';
import { Head } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { Download, FileAudio, FileText, FileVideo } from 'lucide-react';

interface PrivateFileShowProps {
    privateFile: PrivateFile & {
        temporary_url: string;
        metadata?: Record<string, unknown>;
    };
}

const getFileIcon = (contentType: string) => {
    switch (contentType) {
        case 'text':
            return <FileText className="h-5 w-5" />;
        case 'audio':
            return <FileAudio className="h-5 w-5" />;
        case 'video':
            return <FileVideo className="h-5 w-5" />;
        default:
            return <FileText className="h-5 w-5" />;
    }
};

export default function PrivateFileShow({ privateFile }: PrivateFileShowProps) {
    return (
        <AppLayout>
            <Head title="View Private File" />
            <PageContainer backUrl={route('private-files.index')} heading={`View Private File`} subheading={`Details for ${privateFile.name}`}>
                <Card>
                    <CardHeader>
                        <CardTitle>File Details</CardTitle>
                        <CardDescription>Information about {privateFile.name}</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        <div className="grid gap-4">
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground text-sm font-medium">Name</span>
                                <span className="text-sm">{privateFile.name}</span>
                            </div>

                            <Separator />

                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground text-sm font-medium">Content Type</span>
                                <div className="flex items-center gap-2">
                                    {getFileIcon(privateFile.content_type)}
                                    <Badge variant="outline">{privateFile.content_type}</Badge>
                                </div>
                            </div>

                            <Separator />

                            {privateFile.description && (
                                <>
                                    <div className="space-y-2">
                                        <span className="text-muted-foreground text-sm font-medium">Description</span>
                                        <p className="text-sm">{privateFile.description}</p>
                                    </div>
                                    <Separator />
                                </>
                            )}

                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground text-sm font-medium">File Name</span>
                                <span className="font-mono text-sm">{privateFile.file_name}</span>
                            </div>

                            <Separator />

                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground text-sm font-medium">MIME Type</span>
                                <span className="text-sm">{privateFile.mime_type}</span>
                            </div>

                            <Separator />

                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground text-sm font-medium">File Size</span>
                                <span className="text-sm">{privateFile.file_size}</span>
                            </div>

                            <Separator />

                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground text-sm font-medium">Created</span>
                                <span className="text-sm">{formatDistanceToNow(new Date(privateFile.created_at), { addSuffix: true })}</span>
                            </div>

                            <Separator />

                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground text-sm font-medium">Last Updated</span>
                                <span className="text-sm">{formatDistanceToNow(new Date(privateFile.updated_at), { addSuffix: true })}</span>
                            </div>

                            {privateFile.metadata && Object.keys(privateFile.metadata).length > 0 && (
                                <>
                                    <Separator />
                                    <div className="space-y-2">
                                        <span className="text-muted-foreground text-sm font-medium">Metadata</span>
                                        <pre className="bg-muted overflow-auto rounded-md p-3 text-sm">
                                            {JSON.stringify(privateFile.metadata, null, 2)}
                                        </pre>
                                    </div>
                                </>
                            )}
                        </div>

                        <div className="pt-4">
                            <Button asChild className="w-full">
                                <a href={privateFile.temporary_url} download={privateFile.file_name}>
                                    <Download className="mr-2 h-4 w-4" />
                                    Download File
                                </a>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </PageContainer>
        </AppLayout>
    );
}
