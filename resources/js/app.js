import './bootstrap';

document.querySelectorAll('textarea[data-wysiwyg]').forEach((textarea) => {
    const wrapper = document.createElement('div');
    wrapper.className = 'space-y-2';

    const toolbar = document.createElement('div');
    toolbar.className = 'flex gap-2';
    toolbar.innerHTML = `
        <button type="button" data-cmd="bold" class="rounded border px-2 py-1">B</button>
        <button type="button" data-cmd="italic" class="rounded border px-2 py-1">I</button>
        <button type="button" data-cmd="insertUnorderedList" class="rounded border px-2 py-1">• List</button>
    `;

    const editor = document.createElement('div');
    editor.className = 'min-h-[120px] rounded border bg-white p-2';
    editor.contentEditable = 'true';
    editor.innerHTML = textarea.value;

    toolbar.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-cmd]');
        if (!button) return;
        document.execCommand(button.dataset.cmd, false);
        editor.focus();
    });

    editor.addEventListener('input', () => {
        textarea.value = editor.innerHTML;
    });

    textarea.style.display = 'none';
    textarea.parentNode.insertBefore(wrapper, textarea);
    wrapper.appendChild(toolbar);
    wrapper.appendChild(editor);
    wrapper.appendChild(textarea);
});
