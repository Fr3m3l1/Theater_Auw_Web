import React from 'react';
import {Button} from '../../components/actions/Button.jsx';
export function Placeholder({label,ratio='16/9',dark=false,style}){
  return <div style={{aspectRatio:ratio,display:'flex',alignItems:'center',justifyContent:'center',borderRadius:'var(--radius-md)',
    border:`1.5px dashed ${dark?'var(--border-onstage)':'var(--border-strong)'}`,color:dark?'var(--text-onstage-muted)':'var(--text-muted)',
    fontFamily:'var(--font-body)',fontSize:13,textAlign:'center',padding:12,...style}}>{label}</div>;
}
export function SiteHeader({phase,onTickets}){
  const nav=['Spieldaten','Theater 2026','Schauspieler','Sponsoren','Rückblick','Berichte'];
  const sale=phase==='vorverkauf'||phase==='spielzeit';
  return <header style={{background:'var(--ink-950)',borderBottom:'1px solid var(--border-onstage)',position:'sticky',top:0,zIndex:40}}>
    <div style={{maxWidth:'var(--container)',margin:'0 auto',padding:'0 24px',height:64,display:'flex',alignItems:'center',gap:28}}>
      <img src="../../assets/logo.png" alt="Theater Auw" style={{height:34}}/>
      <nav style={{display:'flex',gap:20,flex:1,justifyContent:'center'}}>
        {nav.map(n=><a key={n} href="#" onClick={e=>e.preventDefault()} style={{color:'var(--text-onstage-muted)',textDecoration:'none',fontFamily:'var(--font-body)',fontSize:14.5,fontWeight:500}}
          onMouseEnter={e=>e.target.style.color='var(--text-onstage)'} onMouseLeave={e=>e.target.style.color='var(--text-onstage-muted)'}>{n}</a>)}
      </nav>
      {sale?<Button size="sm" onClick={onTickets}>Tickets kaufen</Button>:<Button size="sm" variant="stage">Newsletter</Button>}
    </div>
  </header>;
}
export function SiteFooter(){
  const link={color:'var(--text-onstage-muted)',textDecoration:'none',fontSize:14,fontFamily:'var(--font-body)'};
  return <footer style={{background:'var(--ink-950)',color:'var(--text-onstage)',padding:'var(--sp-16) 24px var(--sp-10)'}}>
    <div style={{maxWidth:'var(--container)',margin:'0 auto',display:'grid',gridTemplateColumns:'2fr 1fr 1fr',gap:40}}>
      <div><img src="../../assets/logo.png" alt="Theater Auw" style={{height:40,marginBottom:12}}/>
        <p style={{margin:0,fontSize:14,color:'var(--text-onstage-muted)',maxWidth:320}}>Die Theatergruppe des STV Auw. Jeden Herbst auf der Bühne — seit 1937.</p></div>
      <div style={{display:'flex',flexDirection:'column',gap:8}}>
        <h3 style={{fontSize:16,color:'var(--text-onstage)',marginBottom:4}}>Kontakt</h3>
        <a href="mailto:theaterauw@bluewin.ch" style={{...link,color:'var(--red-500)'}}>theaterauw@bluewin.ch</a>
      </div>
      <div style={{display:'flex',flexDirection:'column',gap:8}}>
        <h3 style={{fontSize:16,color:'var(--text-onstage)',marginBottom:4}}>Information</h3>
        <a href="#" onClick={e=>e.preventDefault()} style={link}>Impressum</a>
        <a href="#" onClick={e=>e.preventDefault()} style={link}>Datenschutz</a>
        <a href="#" onClick={e=>e.preventDefault()} style={link}>STV Auw</a>
      </div>
    </div>
    <div style={{maxWidth:'var(--container)',margin:'32px auto 0',paddingTop:20,borderTop:'1px solid var(--border-onstage)',fontSize:13,color:'var(--text-onstage-muted)'}}>© 2026 Theater Auw</div>
  </footer>;
}
