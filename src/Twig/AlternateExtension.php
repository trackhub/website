<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * See https://support.google.com/webmasters/answer/189077?hl=en
 */
class AlternateExtension extends AbstractExtension
{
    private ?Request $request;
    private UrlGeneratorInterface $router;

    public function __construct(UrlGeneratorInterface $router, RequestStack $requestStack = null)
    {
        if ($requestStack) {
            $this->request = $requestStack->getCurrentRequest();
        }
        $this->router = $router;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('app_meta_alternate', [$this, 'alternate']),
        ];
    }

    public function alternate(): string
    {
        if ($this->request === null) {
            return '';
        }

        $currentUri = $this->request->getRequestUri();

        // do not add alternates for non-translatable urls
        if (!preg_match('#^/[a-z]{2}[/$]#', $currentUri)) {
            return '';
        }

        $return = '';

        foreach (['en', 'bg'] as $locale) {
            if ($locale === $this->request->getLocale()) {
                continue;
            }

            $alternateLocaleUrl = preg_replace('#^/[a-z]{2}#', "/$locale", $currentUri);
            $escapedUrl = htmlspecialchars($alternateLocaleUrl, ENT_QUOTES);
            $return .= '<link rel="alternate" href="'. $escapedUrl .'" hreflang="' . $locale . '">' . PHP_EOL;
        }

        return $return;
    }
}
