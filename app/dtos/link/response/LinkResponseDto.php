<?php
class LinkResponseDto
{
    public $id_link;
    public $title;

    public $type;
    public $url;

    public $page_id;

    public $created_at;
    public $updated_at;

    public $new_tab;

    public $page;

    public $file_url;

    public function __construct($link)
    {
        $fileUploader = new FileUploader();
        $this->id_link = $link->id_link;
        $this->title = $link->title;
        $this->type = $link->type;
        $this->url = $link->url;
        $this->page_id = $link->page_id;
        $this->created_at = $link->created_at;
        $this->updated_at = $link->updated_at;
        $this->new_tab = $link->new_tab;
        $this->page = isset($link->page) ? $link->page : null;
        $this->file_url = isset($link->file_path) ? $fileUploader->getUrl($link->file_path, 'files') : null;
    }
}