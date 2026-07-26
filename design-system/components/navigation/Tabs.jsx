import React,{useState} from 'react';
export function Tabs({tabs=[],active,onChange,dark=false}){
  const [hov,setHov]=useState(null);
  return <div role="tablist" style={{display:'flex',gap:4,borderBottom:`1px solid ${dark?'var(--border-onstage)':'var(--border-subtle)'}`,fontFamily:'var(--font-display)'}}>
    {tabs.map(t=>{const on=t===active;
      return <button key={t} role="tab" aria-selected={on} onClick={()=>onChange&&onChange(t)} onMouseEnter={()=>setHov(t)} onMouseLeave={()=>setHov(null)}
        style={{padding:'10px 16px',fontSize:16,fontWeight:600,textTransform:'uppercase',letterSpacing:'.05em',fontFamily:'inherit',cursor:'pointer',
        background:'none',border:'none',borderBottom:`2px solid ${on?'var(--red-600)':'transparent'}`,marginBottom:-1,
        color:on?'var(--red-600)':hov===t?(dark?'var(--text-onstage)':'var(--text-body)'):(dark?'var(--text-onstage-muted)':'var(--text-muted)'),
        transition:'color var(--dur-fast)'}}>{t}</button>;})}
  </div>;
}
