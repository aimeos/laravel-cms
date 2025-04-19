<div class=cms-image-text">
	@includeIf('cms::image', $image ?? [] )
	<div class="cms-text">
		<?= (new \League\CommonMark\GithubFlavoredMarkdownConverter([
				'html_input' => 'escape',
				'allow_unsafe_links' => false,
				'max_nesting_level' => 25
			]))->convert($text ?? '')
		?>
	</div>
</div>