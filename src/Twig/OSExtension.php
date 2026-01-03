<?php
namespace App\Twig;

use App\Feature\Shared\Domain\Common;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class OSExtension extends AbstractExtension {

    private Common $common;

    public function __construct(string|null $appId= null)
    {
        $this->common = new Common($appId);
    }

    public function getFilters(): array
    {
        return [
			new TwigFilter('imagecolor', [$this->common, 'imagecolor']),
			new TwigFilter('imagesize', [$this->common, 'imagesize']),
			new TwigFilter('filesize', [$this->common, 'filesize']),
			new TwigFilter('pretty_size', [$this->common, 'prettySize']),
			new TwigFilter('pretty_date', [$this->common, 'prettyDate']),
			new TwigFilter('pretty_datetime', [$this->common, 'prettyDateTime']),
			new TwigFilter('slug', [$this->common, 'slug']),
			new TwigFilter('frdate', [$this->common, 'FrDate']),
			new TwigFilter('frdatetime', [$this->common, 'FrDateTime']),
			new TwigFilter('frmonth', [$this->common, 'FrMonth']),
			new TwigFilter('strftime', [$this->common, 'Strftime']),
			new TwigFilter('mobile', [$this->common, 'mobile']),
			new TwigFilter('desktop', [$this->common, 'desktop']),
			new TwigFilter('country', [$this->common, 'country']),
			new TwigFilter('youtubeId', [$this->common, 'youtubeID']),
			new TwigFilter('youtubeThumbnail', [$this->common, 'youtubeThumbnail']),
            new TwigFilter('filterArray', [$this->common, 'filterArray']),
            new TwigFilter('summary', [$this->common, 'summary']),
            new TwigFilter('search', [$this, 'arraySearch']),
            new TwigFilter('dateDiff', [$this, 'dateDiff']),
            new TwigFilter('price', [$this, 'formatPrice']),
            new TwigFilter('ellipse', [$this, 'ellipseString']),
            new TwigFilter('startWith', [$this, 'startWith']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('isMobile', [$this->common, 'isMobile']),
            new TwigFunction('isDesktop', [$this->common, 'isDesktop']),
            new TwigFunction('mobile', [$this->common, 'mobile']),
            new TwigFunction('desktop', [$this->common, 'desktop']),
            new TwigFunction('downloadPDF', [$this->common, 'downloadPDF']),
            new TwigFunction('youtubeThumbnail', [$this->common, 'youtubeThumbnail']),
            new TwigFunction('filterArray', [$this->common, 'filterArray']),
            new TwigFunction('os_param', [$this->common, 'getParameter']),
            new TwigFunction('field_attr', [$this, 'formFieldAttributes']),
        ];
    }

    public function formFieldAttributes($formField)
    {
        // render as html attributes
        $attributes = [];
        foreach ($formField->vars['attr'] as $key => $value) {
            if($key === "required"){
                if($value){
                    $attributes[] = $key . "=" . $key;
                }
            }
            else if($value !== null){
                $attributes[] = $key . '=' . $value;
            }
        }

        return implode(' ', $attributes);
    }

    public function arraySearch($v, $t){
		return array_search($v,$t);
	}

    public function dateDiff($date1, $date2= new \DateTime('now')): string
    {
        $diff = $date2->diff($date1);
        $diff->format('%a');
        return $diff->format('%a');
    }

    public function formatPrice(float $price): string
    {
        return number_format($price, 2, ',', ' '). " F CFA";
    }

    public function ellipseString($value, $length= 200): string
    {
        if (strlen($value) > $length) {
            $value = substr($value, 0, $length) . '...';
        }

        return $value ?? "";
    }

    public function startWith($value, $needle): bool
    {
        return str_starts_with($value, $needle);
    }

    public function getName(){
        return 'os_extension';
    }
}
