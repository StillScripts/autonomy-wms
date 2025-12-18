"use client"

import * as React from "react"
import { X } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { Input } from "@/components/ui/input"
import { cn } from "@/lib/utils"

interface TagInputProps {
  placeholder?: string
  value?: string[]
  onChange?: (tags: string[]) => void
  className?: string
  disabled?: boolean
}

export function TagInput({
  placeholder = "Add tags...",
  value = [],
  onChange,
  className,
  disabled = false,
}: TagInputProps) {
  const [inputValue, setInputValue] = React.useState("")
  const [tags, setTags] = React.useState<string[]>(value)
  const inputRef = React.useRef<HTMLInputElement>(null)

  React.useEffect(() => {
    setTags(value)
  }, [value])

  const addTag = (tag: string) => {
    const trimmedTag = tag.trim()
    if (trimmedTag && !tags.includes(trimmedTag)) {
      const newTags = [...tags, trimmedTag]
      setTags(newTags)
      onChange?.(newTags)
    }
    setInputValue("")
  }

  const removeTag = (tagToRemove: string) => {
    const newTags = tags.filter((tag) => tag !== tagToRemove)
    setTags(newTags)
    onChange?.(newTags)
  }

  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter" || e.key === ",") {
      e.preventDefault()
      addTag(inputValue)
    } else if (e.key === "Backspace" && inputValue === "" && tags.length > 0) {
      removeTag(tags[tags.length - 1])
    }
  }

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setInputValue(e.target.value)
  }

  const handleInputBlur = () => {
    if (inputValue.trim()) {
      addTag(inputValue)
    }
  }

  return (
    <div
      className={cn(
        "flex min-h-10 w-full flex-wrap gap-2 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2",
        disabled && "cursor-not-allowed opacity-50",
        className,
      )}
      onClick={() => inputRef.current?.focus()}
    >
      {tags.map((tag) => (
        <Badge key={tag} variant="secondary" className="gap-1">
          {tag}
          {!disabled && (
            <button
              type="button"
              onClick={(e) => {
                e.stopPropagation()
                removeTag(tag)
              }}
              className="ml-1 rounded-full outline-none ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2"
              aria-label={`Remove ${tag} tag`}
            >
              <X className="h-3 w-3" />
            </button>
          )}
        </Badge>
      ))}
      <Input
        ref={inputRef}
        type="text"
        placeholder={tags.length === 0 ? placeholder : ""}
        value={inputValue}
        onChange={handleInputChange}
        onKeyDown={handleKeyDown}
        onBlur={handleInputBlur}
        disabled={disabled}
        className="flex-1 border-0 bg-transparent p-0 shadow-none focus-visible:ring-0 focus-visible:ring-offset-0"
        aria-label="Add tags"
      />
    </div>
  )
}
