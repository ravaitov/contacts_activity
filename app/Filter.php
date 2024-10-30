<?php

namespace App;

class Filter
{
    private array $list;
    private string $name;
    private string $id;

    public string $out = '';

    public function __construct(string $id, string $name, array|AbstractApp $source)
    {
        $this->id = $id;
        $this->name = $name;
        if (is_array($source)) {
            $this->list = $source;
        } else {
            $source->run();
            $this->list = $source->result;
        }
    }

    public function run(bool $echo = false): void
    {
        $this->out = <<<EOD
            <div class="col-xs-3">
            <label for="$this->id">$this->name:&nbsp;</label>
            <select id="$this->id">
                <option value="default" selected>все
                </option>\n
        EOD;
        foreach ($this->list as $value => $item) {
            $this->out .= "<option value=\"$value\">$item</option>\n";
        }
        $this->out .= "</select>\n</div>\n";
        if ($echo)
            echo $this->out;
    }
}