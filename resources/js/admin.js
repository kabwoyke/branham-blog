import EasyMDE from 'easymde';
import 'easymde/dist/easymde.min.css';
import { htmlToMarkdown, isSafeUrl, sanitizeHtml } from './rich-text';

window.richText = { htmlToMarkdown, isSafeUrl, sanitizeHtml };

// Text-labelled toolbar so no icon font is downloaded from a CDN.
window.makeMarkdownEditor = (element, initialValue, onChange) => {
    const editor = new EasyMDE({
        element,
        initialValue,
        autoDownloadFontAwesome: false,
        spellChecker: false,
        status: ['words'],
        minHeight: '420px',
        placeholder: 'Write in Markdown…',
        toolbar: [
            { name: 'bold', action: EasyMDE.toggleBold, text: 'B', title: 'Bold' },
            { name: 'italic', action: EasyMDE.toggleItalic, text: 'I', title: 'Italic' },
            { name: 'heading-2', action: EasyMDE.toggleHeading2, text: 'H2', title: 'Heading' },
            { name: 'heading-3', action: EasyMDE.toggleHeading3, text: 'H3', title: 'Subheading' },
            '|',
            { name: 'quote', action: EasyMDE.toggleBlockquote, text: 'Quote', title: 'Quote' },
            { name: 'unordered-list', action: EasyMDE.toggleUnorderedList, text: 'List', title: 'Bulleted list' },
            { name: 'ordered-list', action: EasyMDE.toggleOrderedList, text: '1. List', title: 'Numbered list' },
            '|',
            { name: 'link', action: EasyMDE.drawLink, text: 'Link', title: 'Insert link' },
            { name: 'image', action: EasyMDE.drawImage, text: 'Image', title: 'Insert image by URL' },
            { name: 'hr', action: EasyMDE.drawHorizontalRule, text: '—', title: 'Divider' },
            '|',
            { name: 'preview', action: EasyMDE.togglePreview, text: 'Preview', title: 'Preview' },
            { name: 'side-by-side', action: EasyMDE.toggleSideBySide, text: 'Split', title: 'Side by side' },
            { name: 'guide', action: 'https://www.markdownguide.org/basic-syntax/', text: 'Help', title: 'Markdown guide' },
        ],
    });

    editor.codemirror.on('change', () => onChange(editor.value()));

    return editor;
};
