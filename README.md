# Gizli İçerik Eklentisi

**Sürüm:** 1.0.0  
**Geliştirici:** Yiğit Serin  
**WordPress Uyumluluğu:** 5.0 ve üzeri  
**Test Edilen Son Sürüm:** 6.5.x

WordPress sitenizdeki belirli yazıların veya kategorilerin içeriğini gizleyerek yerine özel bir resim ve (isteğe bağlı olarak) bu resme tıklanabilir bir bağlantı gösteren bir eklentidir.

## Özellikler

- **İçerik Gizleme:** Belirli yazıları veya kategorileri gizler.
- **Özel Gösterim:** Gizlenen içeriğin yerine yönetici panelinden ayarlanabilen bir resim ve bu resme atanabilen bir bağlantı gösterir.
- **Esnek Seçim:**
  - Yazıları tek tek düzenleme ekranlarındaki meta kutusu ile gizli olarak işaretleme.
  - Kategorileri tek tek düzenleme/ekleme ekranlarındaki seçenek ile gizli olarak işaretleme.
- **Yönetici Ayarları:**
  - Eklentiyi genel olarak aktif/pasif yapma.
  - Googlebot gibi arama motoru botları için gizlemeyi devre dışı bırakma seçeneği.
  - Gizli içerik yerine gösterilecek resmi medya kütüphanesinden seçme.
  - Gösterilecek resme tıklanıldığında yönlendirilecek URL'yi belirleme.
- **ElasticPress Uyumu:** Gizli içerik bilgisini (`hidden_content` meta alanı olarak) yazıların ElasticPress index'ine ekler, böylece arama sonuçlarında gizli içerikler filtrelenebilir veya buna göre işlem yapılabilir.
- **Sadece Frontend'de Çalışma:** İçerik gizleme filtresi sadece sitenin ön yüzünde çalışır; yönetici paneli, REST API, WP-CLI veya ElasticPress indexleme işlemlerini etkilemez.
- **Giriş Yapmış Kullanıcılar:** İçerik gizleme, giriş yapmamış kullanıcılara uygulanır. Giriş yapmış kullanıcılar içeriği her zaman görür.

## Kurulum

1. Eklenti dosyalarını `.zip` olarak indirin.
2. WordPress yönetici panelinizden **Eklentiler > Yeni Ekle** bölümüne gidin.
3. **Eklenti Yükle** butonuna tıklayın ve indirdiğiniz `.zip` dosyasını seçin.
4. **Hemen Yükle** butonuna tıklayın.
5. Yükleme tamamlandıktan sonra **Eklentiyi Etkinleştir** butonuna tıklayın.
6. Ayarlarını yapılandırmak için **Ayarlar > Gizli İçerik Ayarları** bölümüne gidin.

## Kullanım

### Ayarlar

Eklentiyi etkinleştirdikten sonra **Ayarlar > Gizli İçerik Ayarları** sayfasından aşağıdaki seçenekleri yapılandırabilirsiniz:

- **Eklenti Durumu:** Eklentinin genel çalışma durumunu açıp kapatabilirsiniz.
- **Google Bot İçin Gizleme:** Bu seçenek işaretli ise, Googlebot sitenizi taradığında içerik gizlenmez ve orijinal içeriği görür.
- **Gizli İçerik Resmi ve Bağlantısı:**
  - **Gizli İçerik Resmi:** Medya kütüphanesinden bir resim seçin. Bu resim, gizlenen içeriğin yerine gösterilecektir.
  - **Resme Verilecek Bağlantı (URL):** Seçtiğiniz resme tıklandığında kullanıcıların yönlendirileceği bir URL belirleyebilirsiniz (isteğe bağlı).

### İçerik Gizleme

- **Yazılar İçin:** Bir yazıyı gizlemek için, yazı düzenleme ekranının sağ tarafındaki (veya Gutenberg için ayarlar panelindeki) "Gizli İçerik" meta kutusundaki "Bu yazının içeriğini gizle" seçeneğini işaretleyin.
- **Kategoriler İçin:** Bir kategoriyi gizlemek için, kategori ekleme/düzenleme ekranındaki "Bu kategorideki yazıların içeriğini gizle" seçeneğini işaretleyin.

### ElasticPress Entegrasyonu

Eklenti, bir yazının kendisi veya ait olduğu kategorilerden herhangi biri gizli olarak işaretlenmişse, o yazı için ElasticPress index'ine `meta.hidden_content` alanını `1` (true) olarak ekler. Eğer gizli değilse `0` (false) olarak ekler. Bu sayede ElasticPress sorgularınızda bu alanı kullanarak gizli içerikleri filtreleyebilirsiniz.

Örnek ElasticPress sorgusu (gizli olmayan içerikleri getirmek için):

```json
{
  "query": {
    "bool": {
      "must_not": [
        {
          "term": {
            "meta.hidden_content": 1
          }
        }
      ]
    }
  }
}
```

**Not:** ElasticPress ayarlarınızda meta alanlarının indexlenmesinin aktif olduğundan emin olun. Değişikliklerin index'e yansıması için `wp elasticpress index --include=post` komutunu çalıştırmanız gerekebilir.

## Sıkça Sorulan Sorular

- **S: Eklenti neden sadece giriş yapmamış kullanıcılara içeriği gizliyor?**
  - **C:** Bu, site yöneticilerinin ve editörlerinin içeriği her zaman görebilmesi için varsayılan bir davranıştır. Gelecekte bu davranış daha esnek hale getirilebilir.

## Destek

Herhangi bir sorun veya öneri için lütfen GitHub Issues sayfası üzerinden bildirimde bulunun.

## Katkıda Bulunma

Katkılarınızı bekliyoruz! Lütfen GitHub Repository üzerinden pull request gönderin.
