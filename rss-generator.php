<?php foreach ($all_items as $item): ?>
    <item>
        <title><![CDATA[<?= $item['title'] ?>]]></title>
        <link><?= htmlspecialchars($item['link']) ?></link>
        <guid><?= htmlspecialchars($item['link']) ?></guid>
        <pubDate><?= $item['pubDate'] ?></pubDate>
        
        <description><![CDATA[
            <?php if (!empty($item['image'])): ?>
                <p><img src="<?= htmlspecialchars($item['image']) ?>" style="max-width:100%; border-radius:8px;"></p>
            <?php endif; ?>
            <?= $item['description'] ?>
            <hr/>
            <p style="margin-top:10px; font-size:0.9em; color:#666;"><strong>📰 <?= htmlspecialchars($item['source']) ?></strong> | <a href="<?= htmlspecialchars($item['source_url']) ?>">Читать источник</a></p>
        ]]></description>
        
        <content:encoded><![CDATA[
            <?php if (!empty($item['image'])): ?>
                <p><img src="<?= htmlspecialchars($item['image']) ?>" style="max-width:100%; border-radius:8px;"></p>
            <?php endif; ?>
            <?= $item['content_encoded'] ?>
            <hr/>
            <p style="margin-top:10px; font-size:0.9em; color:#666;"><strong>📰 <?= htmlspecialchars($item['source']) ?></strong> | <a href="<?= htmlspecialchars($item['source_url']) ?>"><?= htmlspecialchars($item['source_url']) ?></a></p>
        ]]></content:encoded>
        
        <?php if (!empty($item['image'])): ?>
            <media:thumbnail url="<?= htmlspecialchars($item['image']) ?>" />
            <media:content url="<?= htmlspecialchars($item['image']) ?>" medium="image" />
        <?php endif; ?>
        
        <dc:creator><?= htmlspecialchars($item['source']) ?></dc:creator>
    </item>
<?php endforeach; ?>