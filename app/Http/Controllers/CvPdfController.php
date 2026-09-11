<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;

class CvPdfController
{
    /** Templates are built around an A4 page: 210mm ≈ 794px at 96dpi. */
    private const PAGE_WIDTH_MM = 210;

    /** A4 width/height at 96dpi, the viewport used to measure the content. */
    private const A4_WIDTH_PX = 794;

    private const A4_HEIGHT_PX = 1123;

    /** Minimum page height — short CVs still come out as a clean A4 sheet. */
    private const MIN_HEIGHT_PX = self::A4_HEIGHT_PX;

    /** Safety cap so a runaway template can't request an absurd page size. */
    private const MAX_HEIGHT_PX = 6000;

    public function download(string $template)
    {
        abort_unless($this->templateExists($template), 404);

        $html = $this->renderedTemplate($template);

        // Measure the content height under print conditions (the templates'
        // own @media print rules drop body padding and page shadows), then
        // print exactly one page of that height. One page = no A4 pagination,
        // so a design can never be sliced mid-element — a CV slightly taller
        // than A4 stays in one piece.
        $heightPx = (float) $this->browsershot($html)
            ->evaluate('Math.ceil(Math.max(document.body.scrollHeight, document.documentElement.scrollHeight))');

        $heightPx = min(max($heightPx, self::MIN_HEIGHT_PX), self::MAX_HEIGHT_PX);

        $pdf = $this->browsershot($html)
            ->paperSize(self::PAGE_WIDTH_MM, $this->pxToMm($heightPx))
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->pdf();

        return response()->make($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->filename().'"',
        ]);
    }

    private function browsershot(string $html): Browsershot
    {
        return Browsershot::html($html)
            ->windowSize(self::A4_WIDTH_PX, self::A4_HEIGHT_PX)
            ->emulateMedia('print')
            ->waitUntilNetworkIdle()
            ->addChromiumArguments([
                '--no-sandbox',
                '--disable-setuid-sandbox'
            ])
            // node isn't on the web server's PATH, so point at it explicitly
            // (see config/services.php) and use the project's own node_modules
            // for puppeteer — there is no global install.
            ->setNodeBinary(config('services.browsershot.node_binary'))
            ->setNodeModulePath(base_path('node_modules'));
    }

    private function renderedTemplate(string $template): string
    {
        $html = view("templates.{$template}")->render();

        // Inline the stylesheet(s) so headless Chrome never fetches assets
        // over HTTP: `php artisan serve` is single-worker and would deadlock
        // on its own asset request, and the vite dev client's HMR websocket
        // keeps network-idle from ever firing. The HMR client is dead weight
        // in a headless render anyway, so drop it too.
        $html = preg_replace_callback(
            '/<link\b[^>]*href="([^"]+\.css[^"]*)"[^>]*>/i',
            fn (array $m) => '<style>'.$this->cssContent($m[1]).'</style>',
            $html,
        );

        $html = preg_replace(
            '/<script\b[^>]*@vite\/client[^>]*><\/script>/i',
            '',
            $html,
        );

        // Every template carries `@page { size: A4; margin: 0; }` for
        // in-browser printing, but Chrome's print path lets that rule
        // hijack fragmentation: content is sliced at A4 boundaries no
        // matter what paper size we pass below, so a CV taller than A4
        // comes out as multiple pages. The paper size belongs to the
        // controller here, so drop the rule.
        return preg_replace('/@page\s*\{[^}]*\}/', '', $html);
    }

    private function cssContent(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        // Built asset served by the app itself — read it straight off disk.
        if (str_starts_with($path, '/build/')) {
            return (string) file_get_contents(public_path(ltrim($path, '/')));
        }

        // Vite dev-server URL — ask vite for the compiled CSS...
        if (! str_starts_with($url, url('/'))) {
            $css = @file_get_contents($url);
            if ($css !== false) {
                return $css;
            }

            // ...and if the dev server is down (stale hot file), fall back to
            // the last build via the manifest.
            $manifest = json_decode((string) file_get_contents(public_path('build/manifest.json')), true);

            return (string) file_get_contents(
                public_path('build/'.$manifest[ltrim($path, '/')]['file'])
            );
        }

        // Anything else served by the app.
        return (string) file_get_contents(public_path(ltrim($path, '/')));
    }

    private function templateExists(string $template): bool
    {
        return (bool) preg_match('/^temp\d+$/', $template)
            && is_file(resource_path("views/templates/{$template}.blade.php"));
    }

    private function pxToMm(float $px): float
    {
        return round($px * 25.4 / 96, 2);
    }

    private function filename(): string
    {
        return Str::slug(auth()->user()->name).'-cv.pdf';
    }
}
