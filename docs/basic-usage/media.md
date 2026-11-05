---
title: Media
sidebar_position: 8
---

A resource can have media collections, picked from the [javaabu/mediapicker](https://github.com/Javaabu/mediapicker) media library:

```bash
php artisan generate:all posts --create --media=featured_image:image,gallery:image:multiple,documents:document:multiple
```

Each collection is `name[:type][:multiple]`:

- **The type** is one of `image` (the default), `video`, `audio`, `document` or `archive`.
- **`multiple`** allows more than one file.

The collections need no columns: mediapicker keeps attachments in its own table.

What's generated:

```php
// app/Models/Post.php
class Post extends Model implements HasAttachments
{
    use InteractsWithAttachments;

    public function registerAttachmentCollections(): void
    {
        $this->addAttachmentCollection('featured_image')
            ->singleFile()
            ->acceptsFile(fn (File $file) => FileTypes::isAllowed($file->mimeType, 'image'));

        $this->addAttachmentCollection('gallery')
            ->acceptsFile(fn (File $file) => FileTypes::isAllowed($file->mimeType, 'image'));
    }
}
```

```php
// app/Stencil/PostForm.php: a section of pickers
Section::make(__('Media'))
    ->schema([
        MediaPickerField::make('featured_image')
            ->label(__('Featured image'))
            ->mediaType('image'),
        MediaPickerField::make('gallery')
            ->label(__('Gallery'))
            ->mediaType('image')
            ->multiple(),
    ]),
```

The form saves the picked media after the post (stencil calls the picker's `saveRelationship()`).

The show page shows the images with `<x-mediapicker::image>`, and lists the other files. The JSON resource of Inertia pages sends their URLs.

The routes file gets the media library of the admin area, once:

```php
Route::middleware('web')->group(function () {
    Route::mediapicker(['prefix' => 'admin/media', 'name' => 'admin.media']);
});
```

# Setting up mediapicker

- Set `media-library.media_model` to `Javaabu\Mediapicker\Models\Media` and run the migrations, as in [mediapicker's setup](https://github.com/Javaabu/mediapicker).
- The admins' model uploads to the library: it implements `MediaOwner`, with `InteractsWithMedia` and `OwnsMedia`.
- When it does, the generated tests also attach media from the user's library. Otherwise they skip media.
- For Inertia pages, share `Mediapicker::pickerConfig()` as `mediapicker` and wrap the app in `MediapickerProvider`. The generated forms pass `StencilMediaPicker` to `<StencilForm components>`.
