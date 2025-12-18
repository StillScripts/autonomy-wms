"use client"

import * as React from "react"
import { useDropzone } from "react-dropzone"
import { X, Upload } from "lucide-react"
import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"

interface FileInputProps extends Omit<React.InputHTMLAttributes<HTMLInputElement>, "value" | "onChange"> {
  onChange?: (files: FileList | null) => void
  value?: FileList | null | string
  className?: string
  filePreview?: string | null
  previewClassName?: string
  dropzoneClassName?: string
  multiple?: boolean
  accept?: string
}

const FileInput = React.forwardRef<HTMLDivElement, FileInputProps>(
  ({ className, previewClassName, dropzoneClassName, onChange, value, disabled, multiple = false, filePreview, ...props }, ref) => {
    const [previews, setPreviews] = React.useState<string[]>(typeof filePreview==='string'?[filePreview]: value === 'string' ? [value] : [])
    const inputRef = React.useRef<HTMLInputElement>(null)

    // Function to update previews from files
    const updatePreviews = React.useCallback((files: FileList | null | string) => {
      if (!files) {
        setPreviews([])
        return
      }

      if (typeof files === 'string') {
        setPreviews([files])
        return
      }

      const newPreviews: string[] = []
      for (let i = 0; i < files.length; i++) {
        const file = files[i]
        if (file instanceof File) {
          const objectUrl = URL.createObjectURL(file)
          newPreviews.push(objectUrl)
        }
      }
      console.log(newPreviews)
      setPreviews(newPreviews)

      return () => {
        newPreviews.forEach(preview => URL.revokeObjectURL(preview))
      }
    }, [])


    const onDrop = React.useCallback(
      (acceptedFiles: File[]) => {
        if (acceptedFiles?.length > 0) {
          const dataTransfer = new DataTransfer()
          if (multiple) {
            acceptedFiles.forEach(file => dataTransfer.items.add(file))
          } else {
            dataTransfer.items.add(acceptedFiles[0])
          }
          const files = dataTransfer.files
          updatePreviews(files) // Update previews immediately
          onChange?.(files)
        }
      },
      [onChange, multiple, updatePreviews],
    )

    const { getRootProps, isDragActive } = useDropzone({
      onDrop,
      accept: props.accept === "*/*" ? undefined : {
        "image/*": [],
        "audio/*": [],
      },
      multiple,
      disabled,
    })

    const handleRemove = (e: React.MouseEvent) => {
      e.stopPropagation()
      setPreviews([]) // Clear previews immediately
      onChange?.(null)
    }

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
      const files = e.target.files
      updatePreviews(files) // Update previews immediately
      onChange?.(files)
    }

    // Filter out any props that shouldn't be applied to input
    const inputProps = { ...props }
    delete inputProps.children
    delete inputProps.dangerouslySetInnerHTML

    return (
      <div className={cn("space-y-2", className)} ref={ref}>
        <div
          {...getRootProps()}
          className={cn(
            "relative flex cursor-pointer flex-col items-center justify-center rounded-md border border-dashed border-input bg-background p-4 transition-colors hover:bg-muted/50",
            isDragActive && "border-muted-foreground/50 bg-muted/50",
            disabled && "cursor-not-allowed opacity-60",
            dropzoneClassName,
          )}
        >
          <input
            ref={inputRef}
            type="file"
            accept={props.accept || "image/*,audio/*"}
            disabled={disabled}
            multiple={multiple}
            className="absolute inset-0 h-full w-full cursor-pointer opacity-0"
            onChange={handleInputChange}
            {...inputProps}
          />

          {previews.length > 0 ? (
            <div className={cn("w-full", multiple ? "grid grid-cols-2 gap-2" : "max-w-[300px]")}>
              {previews.map((preview, index) => (
                <div key={index} className="relative">
                  <div className={cn("relative overflow-hidden rounded-md", previewClassName)}>
                    <img
                      src={preview}
                      alt={`Preview ${index + 1}`}
                      width={multiple ? 150 : 300}
                      height={multiple ? 150 : 300}
                      className="h-auto w-full object-cover transition-all"
                    />
                    {!disabled && (
                      <Button
                        type="button"
                        variant="destructive"
                        size="icon"
                        className="absolute right-2 top-2 h-6 w-6 rounded-full"
                        onClick={handleRemove}
                      >
                        <X className="h-4 w-4" />
                        <span className="sr-only">Remove image</span>
                      </Button>
                    )}
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="flex flex-col items-center justify-center space-y-2 text-center">
              <div className="flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                <Upload className="h-6 w-6 text-muted-foreground" />
              </div>
              <div className="text-sm font-medium">
                <span className="text-primary">Click to upload</span> or drag and drop
              </div>
              <p className="text-xs text-muted-foreground">
                {multiple ? "Multiple files allowed" : "Single file only"} (SVG, PNG, JPG or GIF, max. 5MB)
              </p>
            </div>
          )}
        </div>
      </div>
    )
  },
)

FileInput.displayName = "FileInput"

export { FileInput }
