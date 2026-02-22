<div class="d-flex align-items-start align-items-sm-center gap-4">
  <div id="{{ $attachmentid }}" style="margin-top: 20px;">
    <img class="imagePreview" src="" style="display:none; width: 100px; height: auto; border: 1px solid #ddd;" />
    <iframe class="pdfPreview" src="" style="display:none; width: 100px; height: 100px;" frameborder="0"></iframe>
  </div>
  <div class="button-wrapper">
    <label for="{{ $name }}" class="btn btn-outline-primary px-3" tabindex="0">
      {{ __('app.browse') }}
      <input type="file" id="{{ $name }}" name="{{ $name }}" class="{{ $inputBoxClass }}" hidden="" accept="image/png, image/jpeg, application/pdf">
    </label>
    <button type="button" class="btn btn-outline-secondary px-3 {{ $attachmentResetClass }}">
      {{ __('app.reset') }}
    </button>
    <p class="text-muted mb-0">{{ __('app.allowsed_size_of_attachment') }}</p>
  </div>
</div>