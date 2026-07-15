<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">
        <?= $record ? 'Edit' : 'Create' ?> <?= $this->e($resourceObj->singular()) ?>
    </h1>
    <a href="/hub/<?= $this->e($resource) ?>" class="text-gray-400 hover:text-white">← Back</a>
</div>

<?php if (!empty($errors)): ?>
<div class="bg-red-900/30 border border-red-700 rounded-lg p-4 mb-4">
    <ul class="space-y-1">
        <?php foreach ($errors as $field => $msgs): ?>
        <?php foreach ((array) $msgs as $msg): ?>
        <li class="text-red-300 text-sm"><?= $this->e($msg) ?></li>
        <?php endforeach; ?>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" action="/hub/<?= $this->e($resource) ?><?= $record ? '/' . $record['id'] : '' ?>" class="space-y-6">
    <div class="bg-gray-800 rounded-lg p-6">
        <div class="space-y-4">
            <?php foreach ($resourceObj->fields() as $field): ?>
            <?php
                $name = $field['name'];
                $type = $field['type'] ?? 'text';
                $label = $field['label'] ?? ucwords(str_replace('_', ' ', $name));
                $value = $old[$name] ?? ($record[$name] ?? ($field['value'] ?? ''));
                $required = !empty($field['required']);
            ?>
            <div>
                <label class="block text-gray-300 text-sm mb-1">
                    <?= $this->e($label) ?>
                    <?php if ($required): ?><span class="text-red-400">*</span><?php endif; ?>
                </label>
                <?php if ($type === 'textarea' || $type === 'richtext'): ?>
                <textarea name="<?= $this->e($name) ?>" rows="<?= $type === 'richtext' ? 10 : 4 ?>" class="w-full bg-gray-700 text-white rounded px-3 py-2"><?= $this->e($value) ?></textarea>
                <?php elseif ($type === 'select'): ?>
                <select name="<?= $this->e($name) ?>" class="w-full bg-gray-700 text-white rounded px-3 py-2">
                    <?php foreach ($field['options'] ?? [] as $opt): ?>
                    <option value="<?= $this->e($opt) ?>" <?= $value === $opt ? 'selected' : '' ?>><?= $this->e($opt) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php elseif ($type === 'toggle' || $type === 'boolean'): ?>
                <label class="flex items-center gap-2">
                    <input type="hidden" name="<?= $this->e($name) ?>" value="0">
                    <input type="checkbox" name="<?= $this->e($name) ?>" value="1" <?= $value ? 'checked' : '' ?> class="rounded bg-gray-700">
                    <span class="text-gray-400 text-sm"><?= $this->e($label) ?></span>
                </label>
                <?php else: ?>
                <input type="<?= $this->e($type === 'slug' ? 'text' : $type) ?>" name="<?= $this->e($name) ?>" value="<?= $this->e($value) ?>" <?= $required ? 'required' : '' ?> class="w-full bg-gray-700 text-white rounded px-3 py-2">
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <button type="submit" class="px-6 py-3 bg-yellow-500 text-black font-bold rounded hover:bg-yellow-400">
        <?= $record ? 'Update' : 'Create' ?> <?= $this->e($resourceObj->singular()) ?>
    </button>
</form>
