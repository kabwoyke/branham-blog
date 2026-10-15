<?php

namespace App\Livewire\Admin\Articles;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\Category;
use App\Support\ImageStore;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class Form extends Component
{
    use WithFileUploads;

    public ?Article $article = null;

    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $body = '';

    public ?int $category_id = null;

    public string $type = 'blog';

    public string $status = 'draft';

    public ?string $published_at = null;

    public bool $is_featured = false;

    public string $meta_title = '';

    public string $meta_description = '';

    public ?TemporaryUploadedFile $image = null;

    public string $featured_image_alt = '';

    public bool $removeImage = false;

    public ?TemporaryUploadedFile $bodyImage = null;

    public string $bodyImageAlt = '';

    public string $bodyImageCaption = '';

    public int $imageInputVersion = 0;

    public function mount(?Article $article = null): void
    {
        if ($article?->exists) {
            Gate::authorize('update', $article);

            $this->article = $article;
            $this->fill([
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'body' => $article->body,
                'category_id' => $article->category_id,
                'type' => $article->type->value,
                'status' => $article->status->value,
                'published_at' => $article->published_at?->format('Y-m-d\TH:i'),
                'is_featured' => $article->is_featured,
                'meta_title' => (string) $article->meta_title,
                'meta_description' => (string) $article->meta_description,
                'featured_image_alt' => (string) $article->featured_image_alt,
            ]);

            return;
        }

        Gate::authorize('create', Article::class);
        $this->article = null;
        $this->category_id = Category::orderBy('id')->value('id');
    }

    public function updatedTitle(string $value): void
    {
        if (! $this->article) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedImage(): void
    {
        try {
            $this->validateOnly('image');
        } catch (ValidationException $exception) {
            $this->image = null;

            throw $exception;
        }
    }

    /**
     * Discard the featured image that was chosen but not yet saved.
     */
    public function clearImage(): void
    {
        $this->reset('image');
        $this->resetErrorBag('image');
        $this->imageInputVersion++;
    }

    /**
     * Discard the supporting image that was chosen but not yet inserted.
     */
    public function clearBodyImage(): void
    {
        $this->reset('bodyImage', 'bodyImageAlt', 'bodyImageCaption');
        $this->resetErrorBag(['bodyImage', 'bodyImageAlt', 'bodyImageCaption']);
        $this->imageInputVersion++;
    }

    public function updatedBodyImage(): void
    {
        try {
            $this->validateOnly('bodyImage', $this->bodyImageRules());
        } catch (ValidationException $exception) {
            $this->bodyImage = null;

            throw $exception;
        }
    }

    /**
     * @return array<string, list<string>>
     */
    private function bodyImageRules(): array
    {
        return [
            'bodyImage' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'bodyImageAlt' => ['required', 'string', 'max:255'],
            'bodyImageCaption' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Store a supporting image and insert it into the editor as Markdown at the cursor.
     */
    public function insertBodyImage(): void
    {
        $this->article
            ? Gate::authorize('update', $this->article)
            : Gate::authorize('create', Article::class);

        $this->validate($this->bodyImageRules(), [
            'bodyImage.required' => 'Choose an image to upload.',
            'bodyImageAlt.required' => 'Describe the image in the alt text field.',
        ]);

        $url = Storage::disk(ImageStore::DISK)->url(ImageStore::store($this->bodyImage, 'articles/body'));

        $this->dispatch('markdown-insert', editor: 'body', markdown: $this->imageMarkdown($url));

        $this->reset('bodyImage', 'bodyImageAlt', 'bodyImageCaption');
    }

    private function imageMarkdown(string $url): string
    {
        $alt = trim(str_replace(['[', ']', "\r", "\n"], ' ', $this->bodyImageAlt));
        $caption = trim(str_replace(['"', "\r", "\n"], ["'", ' ', ' '], $this->bodyImageCaption));

        $title = $caption !== '' ? " \"{$caption}\"" : '';

        return "\n\n![{$alt}]({$url}{$title})\n\n";
    }

    public function updatedSlug(string $value): void
    {
        $this->slug = Str::slug($value);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $hasImage = $this->image !== null || ($this->article?->featured_image && ! $this->removeImage);

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('articles', 'slug')->ignore($this->article?->id)],
            'excerpt' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'type' => ['required', Rule::enum(ArticleType::class)],
            'status' => ['required', Rule::enum(ArticleStatus::class)],
            'published_at' => [Rule::requiredIf($this->status === ArticleStatus::Scheduled->value), 'nullable', 'date', $this->status === ArticleStatus::Scheduled->value ? 'after:now' : 'date'],
            'is_featured' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'featured_image_alt' => [Rule::requiredIf($hasImage), 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'featured_image_alt.required' => 'Describe the image in the alt text field.',
            'published_at.after' => 'A scheduled article needs a publish date in the future.',
            'slug.regex' => 'Use lowercase letters, numbers and single hyphens only.',
        ];
    }

    public function save(): void
    {
        $this->article
            ? Gate::authorize('update', $this->article)
            : Gate::authorize('create', Article::class);

        $this->slug = Str::slug($this->slug ?: $this->title);
        $validated = $this->validate();

        $article = $this->article ?? new Article(['user_id' => auth()->id()]);
        $status = ArticleStatus::from($this->status);

        $article->fill([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'],
            'body' => $validated['body'],
            'category_id' => $validated['category_id'],
            'type' => $validated['type'],
            'status' => $status,
            'is_featured' => $validated['is_featured'],
            'meta_title' => $validated['meta_title'] ?: null,
            'meta_description' => $validated['meta_description'] ?: null,
            'published_at' => $this->resolvePublishedAt($status, $article),
        ]);

        $this->applyImage($article);
        $article->save();

        session()->flash('status', $status === ArticleStatus::Published
            ? 'Article published.'
            : ($status === ArticleStatus::Scheduled ? 'Article scheduled.' : 'Draft saved.'));

        $this->redirectRoute('admin.articles.index', navigate: false);
    }

    private function resolvePublishedAt(ArticleStatus $status, Article $article): ?Carbon
    {
        return match ($status) {
            ArticleStatus::Scheduled => Carbon::parse($this->published_at),
            ArticleStatus::Published => $article->published_at ?? now(),
            ArticleStatus::Draft => null,
        };
    }

    private function applyImage(Article $article): void
    {
        if ($this->image) {
            ImageStore::delete($article->featured_image);
            $article->featured_image = ImageStore::store($this->image);
            $article->featured_image_alt = $this->featured_image_alt;

            return;
        }

        if ($this->removeImage) {
            ImageStore::delete($article->featured_image);
            $article->featured_image = null;
            $article->featured_image_alt = null;

            return;
        }

        if ($article->featured_image) {
            $article->featured_image_alt = $this->featured_image_alt;
        }
    }

    public function render(): View
    {
        return view('livewire.admin.articles.form', [
            'categories' => Category::orderBy('name')->get(),
            'types' => ArticleType::cases(),
            'statuses' => ArticleStatus::cases(),
        ])->title($this->article ? 'Edit article' : 'New article');
    }
}
