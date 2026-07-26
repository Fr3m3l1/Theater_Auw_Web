import React,{useState} from 'react';
export function Input({label,hint,error,type='text',style,...rest}){
  const [foc,setFoc]=useState(false);
  return <label style={{display:'flex',flexDirection:'column',gap:6,fontFamily:'var(--font-body)',fontSize:14,fontWeight:600,color:'var(--text-body)'}}>
    {label}
    <input type={type} onFocus={()=>setFoc(true)} onBlur={()=>setFoc(false)}
      style={{padding:'10px 12px',fontSize:15,fontWeight:400,fontFamily:'var(--font-body)',color:'var(--text-body)',background:'var(--surface-card)',
      border:`1px solid ${error?'var(--red-600)':foc?'var(--red-600)':'var(--border-strong)'}`,borderRadius:'var(--radius-sm)',outline:'none',
      boxShadow:foc?'var(--focus-ring)':'none',transition:'box-shadow var(--dur-fast)',...style}} {...rest}/>
    {(error||hint)&&<span style={{fontSize:13,fontWeight:400,color:error?'var(--red-600)':'var(--text-muted)'}}>{error||hint}</span>}
  </label>;
}
