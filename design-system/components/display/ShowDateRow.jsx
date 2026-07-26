import React,{useState} from 'react';
export function ShowDateRow({weekday,date,time='20.00 Uhr',note,status='free',onAction,actionLabel='Tickets',dark=false}){
  const [hov,setHov]=useState(false);
  const statusEl={
    free:null,
    few:<span style={{fontSize:13,fontWeight:600,color:'var(--warning)'}}>Wenige Plätze</span>,
    soldout:<span style={{fontSize:13,fontWeight:600,color:dark?'var(--text-onstage-muted)':'var(--text-muted)'}}>Ausverkauft</span>,
    past:<span style={{fontSize:13,fontWeight:600,color:dark?'var(--text-onstage-muted)':'var(--text-muted)'}}>Vorbei</span>,
  }[status];
  const dim=status==='soldout'||status==='past';
  return <div onMouseEnter={()=>setHov(true)} onMouseLeave={()=>setHov(false)}
    style={{display:'flex',alignItems:'center',gap:16,padding:'14px 16px',borderRadius:'var(--radius-md)',fontFamily:'var(--font-body)',
    background:hov?(dark?'rgba(248,246,242,.06)':'var(--surface-sunken)'):'transparent',transition:'background var(--dur-fast)',opacity:dim?.55:1}}>
    <div style={{fontFamily:'var(--font-display)',fontWeight:700,fontSize:15,textTransform:'uppercase',letterSpacing:'.06em',color:'var(--red-600)',width:34}}>{weekday}</div>
    <div style={{fontFamily:'var(--font-display)',fontWeight:600,fontSize:22,textTransform:'uppercase',color:dark?'var(--text-onstage)':'var(--text-body)',width:64}}>{date}</div>
    <div style={{fontSize:14,color:dark?'var(--text-onstage-muted)':'var(--text-muted)',flex:1}}>{time}{note&&<span style={{marginLeft:8,fontWeight:600,color:dark?'var(--gold-400)':'var(--red-600)'}}>{note}</span>}</div>
    {statusEl}
    {onAction&&!dim&&<button onClick={onAction} style={{padding:'7px 16px',fontSize:14,fontWeight:600,fontFamily:'var(--font-body)',color:'#fff',background:hov?'var(--accent-hover)':'var(--accent)',border:'none',borderRadius:'var(--radius-sm)',cursor:'pointer',transition:'background var(--dur-fast)'}}>{actionLabel}</button>}
  </div>;
}
