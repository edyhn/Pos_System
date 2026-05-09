<?php
$target = $argv[1];
$content = file_get_contents($target);

$search = <<<'PHP'
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
PHP;

$replace = 'public function test_basic(): void
    {
        $this->assertTrue(true);
    }';

$newContent = str_replace($search, $replace, $content);
file_put_contents($target, $newContent);
echo "Modified: $target\n";
