# PHP için llms.txt

[![tests](https://github.com/irodevltd/llms-txt-php/actions/workflows/tests.yml/badge.svg)](https://github.com/irodevltd/llms-txt-php/actions/workflows/tests.yml)
![PHP](https://img.shields.io/badge/php-%3E%3D8.1-777bb4)
![License](https://img.shields.io/badge/license-MIT-blue)

[English](README.md) · **Türkçe**

Sitenizin `/llms.txt`, `/llms-full.txt` ve `/AGENTS.md` dosyalarını **kendi verinizden** üretir; böylece dosyalar
sayfalarınızla hiçbir zaman çelişmez. Herhangi bir llms.txt dosyasını [öneriye](https://llmstxt.org) göre denetler
ve dosyaları doğru başlıklarla sunar. Bağımlılığı yoktur.

```php
use Irodev\LlmsTxt\LlmsTxt;

echo LlmsTxt::create('Örnek Stüdyo')
    ->summary('Örnek Stüdyo küçük işletmeler için web siteleri ve e-ticaret mağazaları kurar.')
    ->details('Örnek Ltd. E-posta: merhaba@ornek.com.')
    ->section('Hizmetler')
    ->link('Kurumsal web siteleri', 'https://ornek.com/hizmetler/web', 'Telefonda hızlı açılan, işletmenize özel siteler.')
    ->link('E-ticaret', 'https://ornek.com/hizmetler/magaza', 'Shopify ya da özel yazılım.')
    ->optional()
    ->link('Yapay zekâ araçları için tam metin', 'https://ornek.com/llms-full.txt');
```

```markdown
# Örnek Stüdyo

> Örnek Stüdyo küçük işletmeler için web siteleri ve e-ticaret mağazaları kurar.

Örnek Ltd. E-posta: merhaba@ornek.com.

## Hizmetler

- [Kurumsal web siteleri](https://ornek.com/hizmetler/web): Telefonda hızlı açılan, işletmenize özel siteler.
- [E-ticaret](https://ornek.com/hizmetler/magaza): Shopify ya da özel yazılım.

## Optional

- [Yapay zekâ araçları için tam metin](https://ornek.com/llms-full.txt)
```

[irodev.net/llms.txt](https://irodev.net/llms.txt) dosyasını üreten kodun bağımsız hâlidir.

## Bu dosyalar nedir, açıkçası

- **llms.txt**, sitenizin kök dizininde duran bir Markdown dosyası için yapılmış bir öneridir (Jeremy Howard,
  Eylül 2024). Dil modellerine sitenizin kısa bir özetini ve en faydalı sayfalarının bağlantılarını verir.
- **llms-full.txt** yaygın bir eşlikçidir: aynı dizin, ardından o sayfaların tam içeriği.
- **AGENTS.md** başlangıçta kod depolarında kodlama ajanlarına yönelik talimatlar içindi. Bir web sitesinde, siteyi
  bir kişi adına okuyan ya da kullanan yapay zekâ ajanları için düz bir sayfadır.

Hiçbir büyük arama motoru llms.txt okuduğunu belgelemiyor; Google da yapay zekâ özellikleri için özel bir dosya
gerekmediğini söylüyor. Bu dosyaları, onları arayan yapay zekâ araçları ve ajanlar için bir kolaylık olarak görün,
sıralama faktörü olarak değil. Asıl işi hâlâ sayfalarınız, robots.txt ve yapısal veri yapar.

## Kurulum

```bash
composer require irodevnet/llms-txt
```

## Kullanım

### llms.txt

`LlmsTxt` önerinin yapısını izler: H1 başlık, `>` özet, isteğe bağlı açıklama paragrafları, ardından not içeren
bağlantı listelerinden oluşan H2 bölümler. "Optional" bölümü her zaman en sona gelir: Daha kısa bir bağlama ihtiyaç
duyan bir araç onu atlayabilir. (Bölüm adının İngilizce kalması önerinin gereğidir.)

Dosyayı sayfalarınızla aynı dizilerden ya da veritabanı satırlarından üretin; CMS'inizden gelen HTML'i
`Markdown::plain()` ile temizleyin. Bağlantı metinleri ve adresleri sizin yerinize kaçışlanır.

### llms-full.txt

```php
use Irodev\LlmsTxt\LlmsFullTxt;

$full = new LlmsFullTxt($index);                       // yukarıdaki LlmsTxt
foreach ($sayfalar as $sayfa) {
    $full->document($sayfa['baslik'], $sayfa['url'], $sayfa['markdown'], $sayfa['guncelleme']);
}
echo $full;
```

Her belge bir yatay çizgiden sonra gelir: başlığı H1 olarak, adresi (ve tarihi) bir sonraki satırda.

### AGENTS.md

```php
use Irodev\LlmsTxt\AgentsMd;

echo AgentsMd::create('AGENTS.md: Örnek Stüdyo')
    ->intro('Bu siteyi bir kişi adına okuyan ya da kullanan yapay zekâ ajanları ve asistanlar için yönergeler.')
    ->section('Neler nerede')
    ->item('Hizmetler: https://ornek.com/hizmetler')
    ->item('İletişim: https://ornek.com/iletisim')
    ->section('Kurallar')
    ->item('Yalnızca adına hareket ettiğiniz kişi istediğinde mesaj gönderin.');
```

### Dosyaları sunmak

```php
use Irodev\LlmsTxt\Response;

Response::text($index->render());      // llms.txt, llms-full.txt: text/plain; charset=utf-8
Response::markdown($agents->render()); // AGENTS.md: text/markdown; charset=utf-8
```

İkisi de `Cache-Control: public, max-age=3600` ve `X-Robots-Tag: noindex` ekler: Bu dosyalar makineler için
rehberdir, arama sonuçlarında çıkacak sayfalar değil; robots.txt yine de tarayıcıların onları almasına izin verir.
Başlıkları kendisi ayarlayan bir framework kullanıyorsanız `Response::headers()` işinizi görür.
[`examples/serve.php`](examples/serve.php) yönlendirme kurallarıyla birlikte eksiksiz bir örnektir.

### Bir dosyayı denetlemek

```php
use Irodev\LlmsTxt\Validator;

$result = Validator::check($metin);    // ['errors' => [...], 'warnings' => [...]]
```

Komut satırından:

```bash
php examples/check.php https://ornek.com/llms.txt
```

Hatalar biçimi bozar: Dosya H1 ile başlamıyordur, birden fazla H1 vardır ya da dosya HTML'dir. Uyarılar eksik özeti,
bağlantı olmayan liste öğelerini, göreli ya da `http://` adresleri ve boş bölümleri gösterir. Mesajlar İngilizcedir.
Doğrulayıcı llms.txt içindir; llms-full.txt'de her belgenin kendi H1'i olması tasarım gereğidir.

## Testler

```bash
composer install
composer test
```

## Lisans

MIT © [İRODEV LTD](https://irodev.net). İrodev; Türkiye ve Birleşik Krallık'taki işletmeler için web siteleri ve
yazılımlar geliştiren bir stüdyodur. Sitenizin yapay zekâ aramalarına ne kadar hazır olduğunu
[ücretsiz AI görünürlük testiyle](https://irodev.net/tr/geo-ai-gorunurluk) görün.
