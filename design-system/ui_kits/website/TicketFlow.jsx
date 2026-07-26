import React from 'react';
import {Button} from '../../components/actions/Button.jsx';
import {Dialog} from '../../components/overlay/Dialog.jsx';
import {Tag} from '../../components/display/Badge.jsx';
const ext=<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>;
export function TicketFlow({open,onClose}){
  return <Dialog open={open} title="Tickets über Ticketleo" onClose={onClose}
    actions={<><Button variant="secondary" onClick={onClose}>Abbrechen</Button>
      <Button onClick={()=>{window.open('https://www.ticketleo.com','_blank');onClose();}}>Weiter zu Ticketleo {ext}</Button></>}>
    <div style={{display:'flex',flexDirection:'column',gap:12}}>
      <p style={{margin:0,fontSize:15}}>Der Ticketverkauf läuft über unseren Partner <strong>Ticketleo</strong>. Dort wählst du Vorstellung, Plätze und Kategorie — die Bestätigung kommt direkt per E-Mail.</p>
      <div style={{display:'flex',gap:8}}><Tag>Platzwahl im Saalplan</Tag><Tag>Kat. A / Kat. B</Tag></div>
      <p style={{margin:0,fontSize:13,color:'var(--text-muted)'}}>An der Abendkasse: Bargeld, Karte oder Twint.</p>
    </div>
  </Dialog>;
}
