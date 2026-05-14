<?php
namespace App\Model;

/**
 * Url model class for managing URLs in the CMS.
 */
class Url extends BaseModel
{

    /**
     * Generate a unique URL based on the given string. If the generated URL already exists in the database, it appends an iterator to make it unique.
     *
     * @param string $url The base string to generate the URL from.
     * @param int|null $iterator An optional iterator to append to the URL for uniqueness.
     * @return string A unique URL.
     */
	public function getUrl( $url, $iterator = null )
	{
        $newUrl = $this->transform($url);
        if ($iterator !== null) {
            $newUrl = $this->transform($url) . "-" . $iterator;
        }

        $blog = $this->getDatabase()->table( "blog" )->where( "url", $newUrl )->fetch();
        $page = $this->getDatabase()->table( "pages" )->where( "url", $newUrl )->fetch();
        $blogCategory = $this->getDatabase()->table( "blog_categories" )->where( "url", $newUrl )->fetch();
        $pdp = $this->getDatabase()->table( "personal_data_protection" )->where( "url", $newUrl )->fetch();
        $termsCondition = $this->getDatabase()->table( "terms_conditions" )->where( "url", $newUrl )->fetch();
        
        if ( $blog || $page || $blogCategory || $pdp || $termsCondition ) {
            if ($iterator === null) {
                $iterator = 1;
            } else {
                $iterator += 1;
            }
            return $this->getUrl( $url, $iterator );
        } else {
            return $this->transform($newUrl);
        }
	}

    /**
     * Transform a string into a URL-friendly format by replacing special characters, converting to lowercase, and replacing non-alphanumeric characters with dashes.
     *
     * @param string $string The input string to transform.
     * @return string The transformed URL-friendly string.
     */
	private function transform($string) 
    {
        $string = str_replace(['Č', 'Ě', 'Š', 'Ř', 'Ž', 'Ď', 'Ť', 'Ň', 'Ŕ', 'Ů', 'Ú'], ['c', 'e', 's', 'r', 'z', 'd', 't', 'n', 'r', 'u', 'u'], $string);
        // convert to entities
        $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
        // regex to convert accented chars into their closest a-z ASCII equivelent
        $string = preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', $string);
        // convert back from entities
        $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
        // replace special characters with their closest counterparts
        $string = str_replace(['č', 'ě', 'š', 'ř', 'ž', 'ď', 'ť', 'ň', 'ŕ', 'ů', 'ú'], ['c', 'e', 's', 'r', 'z', 'd', 't', 'n', 'r', 'u', 'u'], $string);
        // any straggling caracters that are not strict alphanumeric are replaced with a dash
        $string = preg_replace('~[^0-9a-z]+~i', '-', $string);
        // trim / cleanup / all lowercase
        $string = trim($string, '-');
        $string = strtolower($string);
        return $string;
    }

    /**
     * Find a URL by its ID and return the URL string.
     *
     * @param string $table The database table to search in.
     * @param int $id The ID of the record to find.
     * @return string|false The URL string if found, or false if not found.
     */
    public function findByTableAndId($table, $id) 
    {
        $data = $this->getDatabase()->table( $table )->where( "id", $id )->limit( 1 )->fetch();

        if ( $data )
            return $data->url;

        return false;
    }

}

/**
 * Url record class representing a single URL entry.
 */
class UrlRecord extends BaseRecord
{


}
