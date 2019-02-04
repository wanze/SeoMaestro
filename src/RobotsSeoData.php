<?php

namespace SeoMaestro;

/**
 * Seo data of the "robots" group.
 */
class RobotsSeoData extends SeoDataBase
{
    /**
     * @var string
     */
    protected $group = 'robots';

    /**
     * @inheritdoc
     */
    protected function renderValue($name, $value)
    {
        return (int) $value;
    }

    /**
     * @inheritdoc
     */
    protected function sanitizeValue($name, $value)
    {
        return (int) $value;
    }

    /**
     * @inheritdoc
     */
    protected function ___renderMetatags(array $data)
    {
        $tags = [];
        $contents = [];

        foreach ($data as $name => $value) {
            if ($value) {
                $contents[] = strtolower($name);
            }
        }

        if (count($contents)) {
            $tags[] = sprintf('<meta name="robots" content="%s">', implode(', ', $contents));
        }

        return $tags;
    }
}
