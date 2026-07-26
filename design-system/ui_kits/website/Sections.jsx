import React from 'react';
import {Card,} from '../../components/display/Card.jsx';
import {Badge,Tag} from '../../components/display/Badge.jsx';
import {ShowDateRow} from '../../components/display/ShowDateRow.jsx';
import {Tabs} from '../../components/navigation/Tabs.jsx';
import {Placeholder} from './Site.jsx';
const wrap={maxWidth:'var(--container)',margin:'0 auto',padding:'0 24px'};
const H2=({children,kicker})=><div style={{marginBottom:28}}>{kicker&&<div style={{fontFamily:'var(--font-body)',fontSize:'var(--text-caption)',letterSpacing:'var(--tracking-caps)',textTransform:'uppercase',fontWeight:600,color:'var(--red-600)',marginBottom:8}}>{kicker}</div>}<h2 style={{fontSize:'var(--text-h1)'}}>{children}</h2></div>;
export const DATES={
  '2026':[{w:'SA',d:'14.11.',note:'Premiere',sponsor:true},{w:'MI',d:'18.11.'},{w:'SA',d:'21.11.'},{w:'SO',d:'22.11.',time:'14.00 Uhr'},{w:'DO',d:'26.11.'},{w:'SA',d:'28.11.',note:'Derniere'}],
  '2027':[{w:'SA',d:'13.11.',note:'Premiere',sponsor:true},{w:'MI',d:'17.11.'},{w:'SA',d:'20.11.'},{w:'SO',d:'21.11.',time:'14.00 Uhr'},{w:'DO',d:'25.11.'},{w:'SA',d:'27.11.',note:'Derniere'}]};
function statusFor(phase,i){
  if(phase==='danke') return 'past';
  if(phase==='spielzeit') return i<4?'past':i===4?'few':'free';
  if(phase==='vorverkauf') return i===0?'few':'free';
  return 'free';
}
export function Spieldaten({phase,onTickets}){
  const [year,setYear]=React.useState('2026');
  const sale=(phase==='vorverkauf'||phase==='spielzeit')&&year==='2026';
  return <section id="daten" style={{background:'var(--surface-page)',padding:'var(--sp-20) 0'}}><div style={wrap}>
    <H2 kicker="Aufführungsdaten">Spieldaten</H2>
    <div style={{maxWidth:720}}>
      <Tabs tabs={['2026','2027']} active={year} onChange={setYear}/>
      <Card pad={false} style={{padding:'8px 8px',marginTop:20}}>
        {DATES[year].map((s,i)=><ShowDateRow key={s.d} weekday={s.w} date={s.d} time={s.time||'20.00 Uhr'} note={s.note}
          status={statusFor(year==='2026'?phase:'vorbereitung',i)} onAction={sale?onTickets:undefined}/>)}
      </Card>
      <p style={{fontSize:14,color:'var(--text-muted)',marginTop:12}}>Premiere jeweils mit Sponsoren-Abend · Türöffnung 18.30 Uhr · Mehrzweckhalle Auw</p>
    </div>
  </div></section>;
}
export function Stueck(){
  return <section style={{background:'var(--surface-card)',padding:'var(--sp-20) 0'}}><div style={{...wrap,display:'grid',gridTemplateColumns:'1.1fr .9fr',gap:56,alignItems:'start'}}>
    <div>
      <H2 kicker="Theater 2026">«s'Jubiläum»</H2>
      <div style={{display:'flex',gap:8,marginBottom:18}}><Tag>Lustspiel · 3 Akte</Tag><Tag>Dieter Gygli</Tag><Tag>Regie: Christine Jauner</Tag></div>
      <p style={{fontSize:16,maxWidth:'56ch',margin:'0 0 14px'}}>Die Rollen sind fast alle verteilt und die Proben laufen — wir sind gespannt, was uns diese Komödie alles zu bieten hat. Mehr möchten wir hier noch nicht verraten.</p>
      <p style={{fontSize:16,maxWidth:'56ch',margin:0}}>Mit Christine Jauner konnten wir eine erfahrene, seit 15 Jahren tätige Theaterpädagogin als Regisseurin engagieren. Sie betreibt ein eigenes Kinder- und Jugendtheater und freut sich, mit uns das Theater 2026 zu erarbeiten.</p>
    </div>
    <Placeholder label="Foto: Ensemble / Probenbild 2026 (bitte Original liefern)" ratio="4/3"/>
  </div></section>;
}
export function Weiteres(){
  return <section style={{background:'var(--surface-page)',padding:'var(--sp-20) 0'}}><div style={wrap}>
    <H2 kicker="Rund um die Vorstellung">Weiteres</H2>
    <div style={{display:'grid',gridTemplateColumns:'repeat(3,1fr)',gap:20}}>
      <Card><h3 style={{fontSize:'var(--text-h3)',marginBottom:8}}>Essen</h3><p style={{margin:0,fontSize:15,color:'var(--text-muted)'}}>Unser Küchenteam ist bei den Abendaufführungen von 18.45 bis 19.30 Uhr bereit, dich kulinarisch zu verwöhnen. Am Sonntag servieren wir nach der Vorstellung.</p></Card>
      <Card><h3 style={{fontSize:'var(--text-h3)',marginBottom:8}}>Tombola</h3><p style={{margin:0,fontSize:15,color:'var(--text-muted)'}}>Auch dieses Jahr gibt es unsere beliebte Tombola. Beim Loskauf wünschen wir dir das nötige Glück zum Soforttreffer — tolle Preise warten auf dich.</p></Card>
      <Card><h3 style={{fontSize:'var(--text-h3)',marginBottom:8}}>Preise & Zahlung</h3><p style={{margin:'0 0 10px',fontSize:15,color:'var(--text-muted)'}}>Preise gemäss Programmheft (Kat. A / Kat. B).</p><div style={{display:'flex',gap:8}}><Tag>Bargeld</Tag><Tag>Karte</Tag><Tag>Twint</Tag></div></Card>
    </div>
  </div></section>;
}
export function Rueckblick(){
  const years=['2025 «Karriere»','2024','2023','2019','2008','1987','1937'];
  return <section style={{background:'var(--surface-stage)',padding:'var(--sp-20) 0'}}><div style={wrap}>
    <div style={{display:'flex',alignItems:'baseline',justifyContent:'space-between',marginBottom:28}}>
      <h2 style={{fontSize:'var(--text-h1)',color:'var(--text-onstage)'}}>Seit 1937 auf der Bühne</h2>
      <a href="#" onClick={e=>e.preventDefault()} style={{color:'var(--red-500)',fontFamily:'var(--font-body)',fontSize:15,fontWeight:600}}>Ganzer Rückblick</a>
    </div>
    <div style={{display:'grid',gridTemplateColumns:'repeat(4,1fr)',gap:16}}>
      {['2025 — «Karriere»','2024','2023','2022'].map(y=><div key={y}>
        <Placeholder dark label={`Foto ${y}`} ratio="4/3"/>
        <div style={{fontFamily:'var(--font-display)',fontWeight:600,fontSize:18,textTransform:'uppercase',color:'var(--text-onstage)',marginTop:10}}>{y}</div>
      </div>)}
    </div>
  </div></section>;
}
