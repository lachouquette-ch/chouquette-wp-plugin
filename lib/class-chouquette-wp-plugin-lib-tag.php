<?php
/*
 * Copyright (c) 2023 by Fabrice Douchant <fabrice.douchant@gmail.com>.
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * Helpers for tags.
 *
 * @since        1.0.0
 * @package        Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/lib
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Lib_Tag
{

    public static $covid = 'covid-19';
    public static $tops = 'tops';

    /**
     * Get the tag id from its slug
     *
     * @param $tag_slug the slug
     * @return int the tag ID or 0 if not found
     */
    public static function get_tag_ID($tag_slug)
    {
        $tag = get_term_by('name', $tag_slug, 'post_tag');
        if ($tag) {
            return $tag->term_id;
        } else {
            return 0;
        }
    }

}
