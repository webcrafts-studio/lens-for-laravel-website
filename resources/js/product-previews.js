const previewDialog = document.getElementById('product-preview-dialog');

if (previewDialog) {
    const previewImage = previewDialog.querySelector('[data-preview-image]');
    const previewTitle = document.getElementById('preview-dialog-title');
    const previewDescription = document.getElementById('preview-dialog-description');
    const previewFile = previewDialog.querySelector('[data-preview-file]');
    const previewScroll = previewDialog.querySelector('.preview-dialog-scroll');
    const sizeButtons = [...previewDialog.querySelectorAll('[data-preview-size]')];
    let previewTrigger;

    const setSize = size => {
        previewDialog.dataset.size = size;
        sizeButtons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.previewSize === size)));
        previewScroll.scrollTo(0, 0);
    };

    document.querySelectorAll('[data-product-preview]').forEach(link => {
        link.addEventListener('click', event => {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) { return; }
            event.preventDefault();
            previewTrigger = link;
            const thumbnail = link.querySelector('img');
            previewImage.src = thumbnail.currentSrc || link.href;
            previewImage.alt = thumbnail.alt;
            previewImage.width = thumbnail.naturalWidth;
            previewImage.height = thumbnail.naturalHeight;
            previewTitle.textContent = link.dataset.previewTitle;
            previewDescription.textContent = link.dataset.previewDescription;
            previewFile.href = previewImage.src;
            setSize('fit');
            previewDialog.showModal();
            document.documentElement.classList.add('preview-open');
        });
    });

    sizeButtons.forEach(button => button.addEventListener('click', () => setSize(button.dataset.previewSize)));
    previewDialog.addEventListener('close', () => {
        document.documentElement.classList.remove('preview-open');
        previewTrigger?.focus({ preventScroll: true });
    });
    previewDialog.addEventListener('click', event => {
        const rect = previewDialog.getBoundingClientRect();
        if (event.target === previewDialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) {
            previewDialog.close();
        }
    });
}
