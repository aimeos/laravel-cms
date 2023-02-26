<div class="cms-markdown">
    <?= (new \League\CommonMark\GithubFlavoredMarkdownConverter( [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 25
        ] ))->convert( $data['text'] ?? '' )
    ?>
</div>