import React from 'react';
import {Button} from '../../components/actions/Button.jsx';
import {Badge} from '../../components/display/Badge.jsx';
const wrap={maxWidth:'var(--container)',margin:'0 auto',padding:'0 24px'};
function Big({children,style}){return <h1 style={{fontSize:'var(--text-poster)',fontWeight:700,lineHeight:'var(--leading-display)',color:'var(--text-onstage)',margin:'18px 0 14px',...style}}>{children}</h1>;}
function Sub({children}){return <p style={{fontSize:'var(--text-lead)',color:'var(--text-onstage-muted)',maxWidth:560,margin:'0 0 28px'}}>{children}</p>;}
function Countdown({days}){
  return <div style={{display:'inline-flex',alignItems:'baseline',gap:10,marginTop:36,color:'var(--text-onstage-muted)',fontFamily:'var(--font-body)',fontSize:14}}>
    <span style={{fontFamily:'var(--font-display)',fontWeight:700,fontSize:44,color:'var(--gold-400)',lineHeight:1}}>{days}</span> Tage bis zur Premiere
  </div>;
}
export function Hero({phase,onTickets,onDates}){
  const stage={background:'var(--surface-stage)',color:'var(--text-onstage)',position:'relative',overflow:'hidden'};
  const inner={...wrap,padding:'88px 24px 96px',position:'relative'};
  if(phase==='vorbereitung') return <section style={stage}><div style={inner}>
    <Badge tone="stage">Vorbereitung läuft</Badge>
    <Big>Theater 2026<br/><span style={{color:'var(--red-500)'}}>«s'Jubiläum»</span></Big>
    <Sub>Lustspiel in 3 Akten von Dieter Gygli. Die Proben laufen — Premiere am SA 14. November 2026 in Auw.</Sub>
    <div style={{display:'flex',gap:12}}><Button variant="stage" size="lg" onClick={onDates}>Spieldaten ansehen</Button><Button variant="ghost" size="lg" style={{color:'var(--red-500)'}}>Berichte lesen</Button></div>
    <Countdown days={121}/>
  </div></section>;
  if(phase==='vorverkauf') return <section style={stage}><div style={inner}>
    <Badge tone="red">Vorverkauf läuft</Badge>
    <Big>Jetzt Tickets<br/>sichern</Big>
    <Sub>«s'Jubiläum» — Lustspiel in 3 Akten. Sechs Vorstellungen vom 14. bis 28. November 2026. Premiere und Sonntag sind schnell weg.</Sub>
    <div style={{display:'flex',gap:12}}><Button size="lg" onClick={onTickets}>Tickets kaufen</Button><Button variant="stage" size="lg" onClick={onDates}>Alle Spieldaten</Button></div>
  </div></section>;
  if(phase==='spielzeit') return <section style={{...stage,backgroundImage:'var(--spotlight)'}}><div style={inner}>
    <Badge tone="gold">Spielzeit — heute Vorstellung</Badge>
    <Big>Heute Abend:<br/>DO 26. November</Big>
    <Sub>Türöffnung 18.30 Uhr, Essen ab 18.45 Uhr, Vorstellung 20.00 Uhr. Es hat noch wenige Plätze — an der Abendkasse oder online.</Sub>
    <div style={{display:'flex',gap:12}}><Button size="lg" onClick={onTickets}>Restplätze buchen</Button><Button variant="stage" size="lg">Speisekarte</Button></div>
    <Countdown days={2}/>
  </div></section>;
  return <section style={stage}><div style={inner}>
    <Badge tone="success">Saison 2026 — das war's</Badge>
    <Big>Merci villmal!</Big>
    <Sub>Sechs ausverkaufte Vorstellungen, ein voller Saal und viele Lacher. Ein herzliches Dankeschön an alle Besucherinnen, Besucher, Helferinnen und Helfer — und an unsere Sponsoren.</Sub>
    <div style={{display:'flex',gap:12}}><Button variant="stage" size="lg">Fotos ansehen</Button><Button variant="ghost" size="lg" style={{color:'var(--red-500)'}}>Rückblick 2026</Button></div>
    <Countdown days={352}/>
  </div></section>;
}
