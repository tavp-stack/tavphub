<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">Hub Dashboard</h1>
</div>

<div class="grid grid-cols-3 gap-4">
    <?php foreach ($resources as $res): ?>
    <a href="/hub/<?= $res['key'] ?>" class="bg-gray-800 rounded-lg p-6 hover:bg-gray-750 transition-colors">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-lg bg-yellow-500/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <div class="text-white font-bold"><?= $this->e($res['label']) ?></div>
                <div class="text-gray-400 text-sm"><?= $this->e($res['singular']) ?></div>
            </div>
        </div>
        <div class="text-3xl font-bold text-white"><?= $res['count'] ?></div>
        <div class="text-gray-500 text-sm">records</div>
    </a>
    <?php endforeach; ?>
</div>
