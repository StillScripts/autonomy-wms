import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { ShieldCheck, TestTubeDiagonal } from 'lucide-react';
import { useState } from 'react';

export type ThirdPartyTab = 'test' | 'live';

const useThirdPartyTab = () => {
    const [tab, setTab] = useState<ThirdPartyTab>('test');
    return { tab, setTab: (value: string) => setTab(value as 'test' | 'live') };
};

export const ThirdPartyTabs = ({ testContent, liveContent }: { testContent: React.ReactNode; liveContent: React.ReactNode }) => {
    const { tab, setTab } = useThirdPartyTab();
    return (
        <Tabs value={tab} onValueChange={(value) => setTab(value as 'test' | 'live')}>
            <TabsList className="mb-4 p-2">
                <TabsTrigger value="test">
                    <TestTubeDiagonal className="h-4 w-4" /> Test Keys
                </TabsTrigger>
                <TabsTrigger value="live">
                    <ShieldCheck className="h-4 w-4" /> Live Keys
                </TabsTrigger>
            </TabsList>
            <TabsContent value="test">{testContent}</TabsContent>
            <TabsContent value="live">{liveContent}</TabsContent>
        </Tabs>
    );
};
