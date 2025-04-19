<div class="cms-paragraph">
	<?= (new \League\CommonMark\GithubFlavoredMarkdownConverter([
			'html_input' => 'escape',
			'allow_unsafe_links' => false,
			'max_nesting_level' => 25
		]))->convert($text ?? '')
	?>
</div>