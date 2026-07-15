<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white"><?= $this->e($resourceObj->label()) ?></h1>
    <a href="/hub/<?= $this->e($resource) ?>/create" class="px-4 py-2 bg-yellow-500 text-black rounded hover:bg-yellow-400">+ Add <?= $this->e($resourceObj->singular()) ?></a>
</div>

<?php if (!empty($_SESSION['cms_flash']['success'])): ?>
<div class="bg-green-900/30 border border-green-700 rounded-lg p-4 mb-4 text-green-300"><?= $this->e($_SESSION['cms_flash']['success']) ?></div>
<?php unset($_SESSION['cms_flash']['success']); endif; ?>

<div class="bg-gray-800 rounded-lg overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-700">
                <?php foreach ($resourceObj->columns() as $col): ?>
                <th class="text-left text-gray-400 text-sm px-4 py-3"><?= $this->e($col['label'] ?? $col['key']) ?></th>
                <?php endforeach; ?>
                <th class="text-left text-gray-400 text-sm px-4 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
            <tr><td colspan="<?= count($resourceObj->columns()) + 1 ?>" class="text-gray-500 px-4 py-4 text-center">No records found.</td></tr>
            <?php else: ?>
            <?php foreach ($records as $record): ?>
            <tr class="border-b border-gray-700 hover:bg-gray-750">
                <?php foreach ($resourceObj->columns() as $col): ?>
                <td class="text-white px-4 py-3"><?= $this->e($record[$col['key']] ?? '') ?></td>
                <?php endforeach; ?>
                <td class="px-4 py-3">
                    <a href="/hub/<?= $this->e($resource) ?>/<?= $record['id'] ?>/edit" class="text-yellow-400 hover:text-yellow-300 text-sm mr-3">Edit</a>
                    <form method="POST" action="/hub/<?= $this->e($resource) ?>/<?= $record['id'] ?>/delete" class="inline" onsubmit="return confirm('Delete this record?')">
                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total > $perPage): ?>
<div class="mt-4 flex justify-center gap-2">
    <?php $pages = ceil($total / $perPage); ?>
    <?php for ($p = 1; $p <= $pages; $p++): ?>
    <a href="/hub/<?= $this->e($resource) ?>?page=<?= $p ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 rounded <?= $p === $page ? 'bg-yellow-500 text-black' : 'bg-gray-700 text-white hover:bg-gray-600' ?>"><?= $p ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
