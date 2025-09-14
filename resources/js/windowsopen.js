// resources/js/windowsopen.js
function encodeSlugSafe(slug) {
    try { slug = decodeURIComponent(slug) } catch (_) {}
    return encodeURIComponent(slug)
}
export default async function openFloatingWindow(sol) {
    const safeTitle = (sol.title ?? ('Решение #' + sol.id)).toString().replace(/</g, '&lt;')
    const encodedSlug = encodeSlugSafe(sol.problem.slug)
    const shareUrl = `${location.origin}/problems/${encodedSlug}/${sol.id}`
    const html = `
    <style>
      html,body{margin:0;height:100%;font-family:ui-sans-serif,system-ui,Segoe UI,Roboto,Arial}
      .wrap{height:100%;display:flex;flex-direction:column}
      header{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid #e5e7eb}
      header h1{font-size:14px;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
      main{flex:1;overflow:auto;padding:16px}
      .content{max-width:980px;margin:0 auto}
      .content *{max-width:100%}
      button{border:1px solid #d1d5db;background:#fff;border-radius:8px;padding:6px 10px;cursor:pointer}
    </style>
    <div class="wrap">
      <header>
        <h1>${safeTitle}</h1>
        <div>
          <button id="shareBtn">Поделиться</button>
          <button id="zoomIn">+</button>
          <button id="zoomOut">−</button>
          <button id="closeBtn">Закрыть</button>
        </div>
      </header>
      <main>
        <div class="content ql-editor">${sol.content ?? ''}</div>
      </main>
    </div>
    <script>
      (function(){
        let scale=1
        const content=document.querySelector('.content')
        document.getElementById('zoomIn').onclick=()=>{ scale=Math.min(2, scale+0.1); content.style.zoom=scale }
        document.getElementById('zoomOut').onclick=()=>{ scale=Math.max(0.6, scale-0.1); content.style.zoom=scale }
        document.getElementById('closeBtn').onclick=()=>{ window.close() }

        const shareData = {
          title: ${JSON.stringify(safeTitle)},
          text: ${JSON.stringify('Посмотри это решение в ProgrammPedia')},
          url: ${JSON.stringify(shareUrl)}
        }

        const shareBtn = document.getElementById('shareBtn')
        shareBtn.onclick = async () => {
          try {
            if (navigator.share) {
              await navigator.share(shareData)
            } else if (navigator.clipboard && window.isSecureContext) {
              await navigator.clipboard.writeText(shareData.url)
              alert('Ссылка скопирована в буфер обмена')
            } else {
              // Фоллбек без HTTPS/клипборда
              const ta = document.createElement('textarea')
              ta.value = shareData.url
              document.body.appendChild(ta)
              ta.select()
              document.execCommand('copy')
              document.body.removeChild(ta)
              alert('Ссылка скопирована в буфер обмена')
            }
          } catch(e) {
            console.warn('Share failed', e)
          }
        }
      })()
    <\/script>
  `

    // DocPiP отключён, чтобы можно было держать несколько окон одновременно
    const hasDocPiP = false

    if (hasDocPiP) {
        try {
            const pipWin = await window.documentPictureInPicture.requestWindow({ width: 1000, height: 700 })
            pipWin.document.write(html)
            pipWin.document.close()
            return
        } catch (e) {
            console.warn('DocPiP error', e)
        }
    }

    // Открываем отдельное окно — можно несколько
    const win = window.open('', '_blank', 'width=1100,height=800,menubar=no,toolbar=no,location=no,status=no')
    if (win) {
        win.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>${safeTitle}</title></head><body>${html}</body></html>`)
        win.document.close()
    }
}
