<?php

namespace TypeRocket\Layout;

use TypeRocket\Html\Generator;
use TypeRocket\Models\SchemaModel;
use TypeRocket\Page;

class Tables
{
    public $results;
    public $columns;
    public $model;
    public $page = null;
    public $settings = ['update_column' => 'id'];

    public function __construct( SchemaModel $model )
    {
        $this->model = $model;
    }

    /**
     * @param array $columns
     *
     * @return Tables
     */
    public function setColumns( array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @param \TypeRocket\Page $page
     *
     * @return $this
     */
    public function setPage( Page $page) {
        $this->page = $page;

        return $this;
    }

    /**
     * Render table
     */
    public function render()
    {
        $results = $this->model->findAll()->get();
        $columns = $this->columns;
        $table = new Generator();
        $head = new Generator();
        $body = new Generator();
        $foot = new Generator();

        if( empty($columns) ) {
            $columns = array_keys(get_object_vars($results[0]));
        }

        $table->newElement('table', ['class' => 'tr-list-table wp-list-table widefat striped']);
        $head->newElement('thead');
        $body->newElement('tbody', ['class' => 'the-list']);
        $foot->newElement('tfoot');

        $th_row = new Generator();
        $th_row->newElement('tr', ['class' => 'manage-column']);
        foreach ( $columns as $column => $data ) {
            $th = new Generator();

            if( ! is_string($column) ) {
                $th->newElement('th', [], ucfirst($data));
            } else {
                $th->newElement('th', [], $data['label']);
            }

            $th_row->appendInside($th);
        }
        $head->appendInside($th_row);
        $foot->appendInside($th_row);

        foreach ( $results as $result ) {
            $td_row = new Generator();
            $td_row->newElement('tr', ['class' => 'manage-column']);
            foreach($columns as $column => $data ) {
                $url = '';

                // get columns if none set
                if( ! is_string($column) ) {
                    $column = $data;
                }

                $text = $result->$column;

                if($this->page instanceof Page && !empty($this->page->pages) ) {
                    foreach ($this->page->pages as $page) {
                        /** @var Page $page */
                        if( $page->action == 'update' ) {
                            $url = $page->getUrl( ['item_id' => (int) $result->id] );
                            break;
                        }
                    }

                    if( !empty($data['actions']) ) {
                        $text = "<strong><a href=\"{$url}\">{$text}</a></strong>";
                        $text .= "<div class=\"row-actions\">";
                        foreach ( $data['actions'] as $index => $action ) {

                            if($index > 0 ) {
                                $text .= ' | ';
                            }

                            switch ($action) {
                                case 'edit' :
                                    $text .= "<span class=\"edit\"><a href=\"{$url}\">Edit</a></span>";
                                    break;
                                case 'delete' :
                                    $text .= "<span class=\"delete\"><a href=\"{$url}\">Delete</a></span>";
                            }
                        }
                        $text .= "</div>";
                    }
                }

                $td = new Generator();
                $td->newElement('td', [], $text);
                $td_row->appendInside($td);
            }
            $body->appendInside($td_row);
        }

        $table->appendInside('thead', [], $head );
        $table->appendInside('tbody', [], $body );
        $table->appendInside('tfoot', [], $foot );

        echo $table;

    }

}