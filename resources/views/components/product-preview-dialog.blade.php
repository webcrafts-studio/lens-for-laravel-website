<dialog id="product-preview-dialog" class="preview-dialog" aria-labelledby="preview-dialog-title" aria-describedby="preview-dialog-description">
    <div class="preview-dialog-heading">
        <h2 id="preview-dialog-title">Lens for Laravel screenshot</h2>
        <form method="dialog"><button type="submit" class="icon-button" aria-label="Close screenshot" autofocus><x-icon name="close"/></button></form>
    </div>
    <div class="preview-dialog-toolbar" role="group" aria-label="Screenshot size">
        <button type="button" data-preview-size="fit" aria-pressed="true">Fit to window</button>
        <button type="button" data-preview-size="original" aria-pressed="false">Original size</button>
        <a data-preview-file href="{{ asset('assets/screenshots/dashboard-light.webp') }}">Open image file <span aria-hidden="true">↗</span></a>
    </div>
    <div class="preview-dialog-scroll" tabindex="0" role="region" aria-label="Screenshot. Use arrow keys to scroll at original size.">
        <img class="preview-dialog-image" data-preview-image alt="" width="1200" height="800">
    </div>
    <p id="preview-dialog-description" class="preview-dialog-description"></p>
</dialog>
