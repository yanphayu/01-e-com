$root = 'D:\project\01-e-com\frontend\src'

'--- toast primitives ---'
foreach ($f in @('stores\toast.js','design-system\BaseToast.vue','design-system\ToastStack.vue','composables\useToast.js')) {
  '{0,-38} {1}' -f ($f -replace '\\','/'), (Test-Path (Join-Path $root $f))
}

'--- App.vue ---'
$app = Get-Content -Raw (Join-Path $root 'App.vue')
"import ToastStack : $([bool]($app -match 'import ToastStack'))"
"<ToastStack/>     : $([bool]($app -match '<ToastStack'))"

'--- converted components ---'
foreach ($t in @('components\products\ProductCreateForm.vue','views\ProductEditView.vue','components\ReportModal.vue')) {
  $f = Join-Path $root $t
  $c = Get-Content -Raw $f
  $imp = [bool]($c -match 'stores/toast')
  $left = ([regex]::Matches($c, 'success\.value\s*=|error\.value\s*=\s*null|class="alert alert-(success|error)"')).Count
  '{0,-44} toastImport={1}  leftoverInline={2}' -f $t, $imp, $left
}
