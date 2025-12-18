import { Button } from "@/components/ui/button";
import { Card, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Link } from "@inertiajs/react";
import { PlusCircle } from "lucide-react";
import { cn } from "@/lib/utils";

export function NewItemCard({ heading, href, buttonText, className, onClick }: { className?: string; heading: string; href?: string; onClick?: () => void; buttonText: string }) {
    return (
    <Card className={cn("hover:border-primary/50 flex flex-col items-center justify-center border-dashed p-6 transition-colors", className)}>
            <CardHeader className="text-center">
                <PlusCircle className="text-muted-foreground mx-auto mb-4 h-12 w-12" />
                <CardTitle className="text-muted-foreground">{heading}</CardTitle>
            </CardHeader>
            <CardFooter>
                {href ? (
                    <Button variant="outline">
                        <Link data-testid="create-content-block-type-button-alt" href={href}>
                        {buttonText}
                    </Link>
                </Button>
                ) : (
                    <Button variant="outline" onClick={onClick}>
                        {buttonText}
                    </Button>
                )}
            </CardFooter>
        </Card>
    );
}
